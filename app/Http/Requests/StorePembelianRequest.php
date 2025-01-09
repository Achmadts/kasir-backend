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
    public function rules(): array
    {
        return [
            'id_produk' => 'required|exists:produks,id',
            'date' => 'date|required',
            'nama_supplier' => 'required',
            'tax' => 'required',
            'discount' => 'required',
            'jumlah_barang' => 'required',
            'status' => 'required|in:Success,Pending,Cancel',
            'payment_method' => 'required|in:Cash,Bank Transfer,Credit Card',
            'total_pembayaran' => 'required',
            'note' => 'nullable'
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
