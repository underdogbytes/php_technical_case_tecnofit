# Technical Case - Tecnofit

# Como rodar o projeto

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


# Decisões tomadas 🔌

## Colunas extras> email (nullable) 💌

Adicionei a coluna email na tabela `accounts` para poder enviar e-mails para os usuários, porque não obrigatoriamente o e-mail do PIX é o mesmo e-mail cadastrado na conta.

Caso não tenha e-mail cadastrado (por erro na etapa de cadastro ou qualquer eventual bug), tomei a decisão de: prosseguir com o PIX para o usuário não ser afetado (e ficar puto no Reclame Aqui), não enviar o e-mail (uma vez que não tem e-mail cadastrado) e emitir um logger avisando sobre a falta de e-mail cadastrado.

## Expansão Futura (Open/Closed Principle) 🤯

Discussão sobre a regra de negócio do trecho:
"Atualmente só existe a opção de saque via PIX, podendo ser somente para chaves do tipo email. A implementação deve possibilitar uma fácil expansão de outras formas de saque no futuro."

Como já existe a tabela `account_withdraw_pix` para armazenar o tipo de chave PIX e no momento só aceita via e-mail, coloquei uma trava de validação no `WithdrawService` para garantir que o tipo de chave seja email.

Caso futuramente possa ser armazenado outros tipos de chaves, basta remover a trava de validação no `WithdrawService`.