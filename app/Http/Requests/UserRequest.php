<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
        $rules = [
            'first_name' => ['required','string','max:255'],
            'middle_name' => ['string','max:255'],
            'last_name' => ['required','string','max:255'],
            'maiden_name' => ['string','max:255'],
            'sex_id' => ['required','exists:sexes,id'],
            'date_birth' => ['required','date_format:Y-m-d'],
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => ['required','string','min:6','confirmed'],
            'type_id' => ['required','exists:types,id']
        ];

        //surrogate age
        if($this->input('type_id') == 2){
            $rules['date_birth'] = ['required','date_format:Y-m-d','before:-21 years','after:-43 years'];
        }


        return $rules;
    }

    public function messages()
    {
        return [
            'date_birth.before' => 'Age must be over 21 years',
            'date_birth.after' => 'Age must be under 42 years'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 400));
    }

}
