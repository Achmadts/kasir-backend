<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenjualanRequest extends FormRequest
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
    public function rules()
    {
        return [
            'nama_pelanggan' => 'required|string|max:255',
            'id_produk' => 'required|array',
            'id_produk.*' => 'required|exists:produks,id',
            'jumlah_produk' => 'required|array',
            'jumlah_produk.*' => 'required|integer|min:1',
            'sub_total' => 'required|array',
            'sub_total.*' => 'required|numeric|min:0',
            'tanggal_penjualan' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'pajak' => 'required|numeric|min:0',
            'diskon' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'status' => 'required|in:Pending,Completed,Cancelled',
            // 'metode_pembayaran' => 'required|in:Cash,Credit Card,Bank Transfer',
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
