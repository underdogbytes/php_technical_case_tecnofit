<?php
declare(strict_types=1);

namespace App\Presentation\Http\Request;

use Hyperf\Validation\Request\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => 'required|string|in:PIX', // Se um dia aceitar outros métodos, altera aqui :)
            'amount' => 'required|numeric|gt:0|max:99999999.99', // Define um teto técnico para evitar abusos
            'pix' => 'required|array',
            'pix.type' => 'required|string|in:email', // Se um dia aceitar outros tipos de chave, altera aqui tambémmm ;)
            'pix.key' => 'required|string|email',
            'schedule' => 'nullable|date_format:Y-m-d H:i|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'method.in' => 'O método de saque deve ser PIX.',
            'amount.required' => 'O valor do saque é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número válido.',
            'amount.gt' => 'O valor do saque deve ser maior que zero.',
            'amount.max' => 'O valor excede o limite permitido para esta transação.',
            'pix.required' => 'Os dados do PIX são obrigatórios.',
            'pix.key.email' => 'A chave PIX deve ser um e-mail válido.',
            'schedule.after' => 'A data de agendamento deve ser uma data futura.',
            'schedule.date_format' => 'O formato da data deve ser YYYY-MM-DD HH:MM:SS.',
        ];
    }
}