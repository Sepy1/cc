<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize()
    {
        // sesuaikan: true sementara, tambahkan auth logic jika perlu
        return true;
    }

    public function rules()
    {
        return [
            'reporter_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'detail' => 'required|string',
            // optional: status cannot disallowed at create (handled by default)
        ];
    }

    public function messages()
    {
        return [
            'reporter_name.required' => 'Nama pelapor wajib diisi.',
            'title.required' => 'Judul tiket wajib diisi.',
            'detail.required' => 'Detail tiket wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
