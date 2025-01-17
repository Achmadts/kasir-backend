<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePembelianRequest extends FormRequest
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
            'id_produk' => 'required|array|min:1',
            'id_produk.*' => 'exists:produks,id',
            'jumlah_produk' => 'required|array|min:1',
            'jumlah_produk.*' => 'integer|min:1',
            'sub_total' => 'required|array|min:1',
            'sub_total.*' => 'numeric|min:0',
            'date' => 'required|date',
            'nama_supplier' => 'required|string',
            'tax' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:Pending,Completed',
            'payment_method' => 'required|in:Cash,Credit Card,Bank Transfer',
            'total_pembayaran' => 'required|numeric|min:0',
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
