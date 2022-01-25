<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SpouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required','string','max:255'],
            'middle_name' => ['string','max:255'],
            'last_name' => ['required','string','max:255'],
            'maiden_name' => ['string','max:255'],
            'sex_id' => ['required','exists:sexes,id'],
            'date_birth' => ['required','date_format:Y-m-d'],
            'email' => ['required','string','email','max:255','unique:users'],
            'marital_id' => ['required','exists:maritals,id']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 400));
    }
}
