<?php

namespace App\Http\Requests;


use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostStore extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'media' => 'json|required',
            'tag_category' => 'array|required',
            'caption' => 'string|nullable|min:0',
            'tagline' => 'string|required|max:250',
            'tag_location' => 'string|required|max:250',

        ];
    }
}

//   protected $fillable = [
//         'user_id',
//         'media',
//         'caption',
//         'tag_category',
//         'tag_location',


//         'tagline'

//     ];
