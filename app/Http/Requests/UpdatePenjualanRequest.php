<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePenjualanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'string|max:255',
            'id_produk' => 'array',
            'id_produk.*' => 'exists:produks,id',
            'jumlah_produk' => 'array',
            'jumlah_produk.*' => 'integer|min:0',
            'sub_total' => 'array',
            'sub_total.*' => 'numeric|min:0',
            'tanggal_penjualan' => 'date',
            'quantity' => 'integer|min:1',
            'pajak' => 'numeric|min:0',
            'diskon' => 'numeric|min:0',
            'total_harga' => 'numeric|min:0',
            'status' => 'in:Pending,Completed,Cancelled',
            'metode_pembayaran' => 'in:Cash,Credit Card,Bank Transfer',
            'catatan' => 'nullable|string',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
