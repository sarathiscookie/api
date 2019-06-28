<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KeyContainerRequest extends FormRequest
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
        switch($this->method()) {
            case 'POST':
            {
                return [
                        'act_number'    => ['required', 'numeric'],
                        'keys'          => ['required'],
                        'shops'         => ['required', 'not_in:0'],
                        'company'       => ['required', 'not_in:0'],
                        'key_type'      => ['required', 'not_in:0'],
                        'key_name'      => ['required', 'string', 'max:100'],
                ]; 
            }
            case 'PUT':
            {
                return [
                    'company'           => ['required', 'not_in:0'],
                ]; 
            }
            default:break;        
        }
    }
}
