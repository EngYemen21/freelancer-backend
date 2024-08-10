<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required',
            'delivery_time' => 'required',
            'file' => [
                'required',
                'mimetypes:image/jpeg,image/png,image/jpg,image/bmp',
            ],
            'category_id'=>'required',
            'skills' => 'required|array',
        ]; }
}
