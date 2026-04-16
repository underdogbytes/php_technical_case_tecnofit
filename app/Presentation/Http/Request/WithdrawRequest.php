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
            'method' => 'required|string|in:PIX',
            'amount' => 'required|numeric|min:0.01',
            'pix' => 'required|array',
            'pix.type' => 'required|string|in:email',
            'pix.key' => 'required|string|email',
            'schedule' => 'nullable|date_format:Y-m-d H:i',
        ];
    }
}
