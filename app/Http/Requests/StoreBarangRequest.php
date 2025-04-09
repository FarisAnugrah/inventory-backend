<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'gudang_id' => 'required|exists:gudang,id',
            'stok_kesuluruhan' => 'required|integer',
            'harga' => 'required|integer',
            'minimum_stok' => 'required|integer',
        ];
    }
}
