<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
                    'zip'       => ['required', 'string', 'max:20'],
                    'city'      => ['required', 'string', 'max:255'],
                    'street'    => ['required', 'string', 'max:255'],
                    'phone'     => ['required', 'string', 'max:20'],
                    'country'   => ['required', 'not_in:0'],
                    'company'   => ['required', 'string', 'max:255']
                ];
            }
            case 'PUT':
            {
                return [
                    'zip'       => ['required', 'string', 'max:20'],
                    'city'      => ['required', 'string', 'max:255'],
                    'street'    => ['required', 'string', 'max:255'],
                    'phone'     => ['required', 'string', 'max:20'],
                    'country'   => ['required', 'not_in:0'],
                    'company'   => ['required', 'string', 'max:255']
                ];
            }
            default:break;
        } 
    }
}
