<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LockSeatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['required', 'integer', 'exists:seats,id'],
        ];
    }
}
