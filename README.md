# Caso Técnico: Tecnofit

Projeto para desafio técnico da vaga de Sênior.

## Tecnologias e Arquitetura
- **PHP 8.4** + **Hyperf Framework** (Swoole-based)
- **MySQL 8.0** (Persistência com Transactional Locks)
- **Redis** (Broker para filas assíncronas)
- **MailHog** (SMTP Testing)
- **Arquitetura:** Baseada em **Clean Architecture** e **Domain-Driven Design (DDD)**, separando responsabilidades em camadas de *Domain*, *Application*, *Infrastructure* e *Presentation*.

<br>

# Como rodar

## Pré-requisitos
- Docker
- Docker Compose

## Passos para executar

1. Clone o repositório:
   ```bash
   git clone https://github.com/underdogbytes/php_technical_case_tecnofit.git
   cd php_technical_case_tecnofit
   ```

2. Crie o arquivo .env no root baseado no .env.example

3. Execute os comandos na ordem:

   ```bash
   docker-compose run --rm --entrypoint /bin/sh app -c "composer install"
   ```

   ```bash
   docker-compose up -d
   ```

   ```bash
   docker-compose exec app php bin/hyperf.php migrate
   ```

(Opcional) Para processar saques agendados, execute o comando:
   ```bash
   docker-compose exec app php bin/hyperf.php app:process-withdrawals
   ```

## Acesso aos serviços
- **Aplicação**: http://localhost:9501
- **MailHog (e-mails de teste)**: http://localhost:8025
- **MySQL**: localhost:3306 (usuário: user, senha: password, banco: tecnofit)
- **Redis**: localhost:6379

## Comandos adicionais
- Ver logs da aplicação: `docker-compose logs --tail=100 -f app`
- Parar os containers: `docker-compose down`
- Deletar pastas forçadamente: `rm -Recurse -Force runtime/container`

<br>

# Arquitetura e Decisões

Comentários sobre decisões tomadas 🔌

## Colunas extras:  email (nullable)

Adicionei a coluna email na tabela `accounts` para poder enviar e-mails para os usuários, porque não obrigatoriamente o e-mail do PIX é o mesmo e-mail cadastrado na conta.

Caso não tenha e-mail cadastrado (por erro na etapa de cadastro ou qualquer eventual bug), tomei a decisão de: prosseguir com o PIX para o usuário não ser afetado (e ficar puto no Reclame Aqui), não enviar o e-mail (uma vez que não tem e-mail cadastrado) e emitir um logger avisando sobre a falta de e-mail cadastrado.


## Expansão Futura (Open/Closed Principle)

Discussão sobre a regra de negócio do trecho:
"Atualmente só existe a opção de saque via PIX, podendo ser somente para chaves do tipo email. A implementação deve possibilitar uma fácil expansão de outras formas de saque no futuro."

Como já existe a tabela `account_withdraw_pix` para armazenar o tipo de chave PIX e no momento só aceita via e-mail, coloquei uma trava de validação no `WithdrawService` para garantir que o tipo de chave seja email.

Caso futuramente possa ser armazenado outros tipos de chaves, basta remover a trava de validação no `WithdrawService`.

<br>

# Testes

## 💸 Saque imediato

**Objetivo:**<br>
Validar se o caminho `Controller -> UseCase -> Repository -> Redis -> MailHog` está conectado.

**Resultado esperado:**<br>
- Status: 201 Created.
- Banco de dados: saldo da conta deve ter diminuído exatamente 100.50.
- MailHog: e-mail de confirmação deve ter chegado.

### Realizar Saque Imediato
`POST /account/{accountId}/balance/withdraw`

**Payload:**
```bash
{
  "method": "PIX",
  "pix": { "type": "email", "key": "teste@teste.com" },
  "amount": 100.50,
  "schedule": null
}
```


## 🗓️ Agendamento Válido

**Objetivo:**<br>
Testa se o sistema diferencia saque imediato de agendado.

**Resultado esperado:**<br>
- Banco: campo scheduled deve ser true.
- Saldo não deve ser descontado ainda e o e-mail não deve ser enviado agora.

### Realizar Agendamento Válido
`POST /account/{accountId}/balance/withdraw`

**Payload:**
```bash
{
  "method": "PIX",
  "pix": { "type": "email", "key": "teste@teste.com" },
  "amount": 50.00,
  "schedule": "2026-12-31 23:59" 
}
```

## Saldo insuficiente

**Objetivo:**<br>
Testa `InsufficientBalanceException`.

**Resultado esperado:**<br>

- Banco: saldo intacto.
- Mensagem: "Saldo insuficiente para realizar o saque".

### 🙅🏻‍♀️💰 Não sacar por falta de saldo
`POST /account/{accountId}/balance/withdraw`

**Payload:**
```bash
{
  "method": "PIX",
  "pix": { "type": "email", "key": "teste@teste.com" },
  "amount": 99999999
}
```

## Validação de Regras de Negócio (Camada de Request)

**Objetivo:**<br>
Quebrar a regra da API.

**Resultado esperado:**<br>
- Ao alterar:
   - **method** "BOLETO" deve exibir a mensagem:
      - "O método de saque deve ser PIX."
   - **amount** -10.00 deve exibir a mensagem:
      - "O valor do saque deve ser maior que zero."
   - **pix.key** inválida deve exibir a mensagem:
      - "A chave PIX deve ser um e-mail válido
   - **schedule** data antiga "2020-01-01 10:00" deve exibir a mensagem:
      - "A data de agendamento deve ser uma data futura."


### 🙅🏻‍♀️📃 Não permitir quebrar as regras
`POST /account/{accountId}/balance/withdraw`

**Observação:** a validação interrompe o ciclo de vida da requisição no primeiro erro encontrado paragarantir integridade.

**Payload:**
```bash
{
  "method": "BOLETO",
  "pix": { "type": "email", "key": "testeeste.com" },
  "amount": -10,
  "schedule": "2020-01-01 10:00"
}
```

## 🙅🏻‍♀️💲 Idempotência e Concorrência

**Objetivo:**<br>
Provar que o lock do banco está funcionando e não dá saque duplicado indevido.

**Resultado esperado:**<br>

- O primeiro saque passa e o segundo retorna erro de saldo insuficiente. Isso prova que seu bloqueio de banco (Lock) está funcionando e você evitou um saque duplicado indevido.

Caso queira verificar o saldo da conta via shell antes de testar via postman/similar em duas abas ao mesmo tempo:

```bash
docker-compose exec mysql mysql -u user -ppassword tecnofit -e "SELECT id, name, balance FROM account;"
```