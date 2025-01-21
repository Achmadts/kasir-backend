<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePembelianRequest extends FormRequest
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
            'id_produk' => 'array|min:1',
            'id_produk.*' => 'exists:produks,id',
            'jumlah_produk' => 'array|min:1',
            'jumlah_produk.*' => 'integer|min:1',
            'sub_total' => 'array|min:1',
            'sub_total.*' => 'numeric|min:0',
            'date' => 'date',
            'nama_supplier' => 'string',
            'tax' => 'numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'quantity' => 'integer|min:1',
            'status' => 'in:Pending,Completed',
            'payment_method' => 'in:Cash,Credit Card,Bank Transfer',
            'total_pembayaran' => 'numeric|min:0',
            'note' => 'nullable|string',
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
