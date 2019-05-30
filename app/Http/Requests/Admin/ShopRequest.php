<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
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
                    'password'          => ['max:255'],
                    'customer_number'   => ['max:100'],
                    'api_key'           => ['max:255'],
                    'mail_password'     => ['required', 'string', 'max:255'],
                    'mail_username'     => ['required', 'string', 'max:100'],
                    'mail_from_name'    => ['required', 'string', 'max:150'],
                    'mail_from_address' => ['required', 'string', 'email', 'max:255'],
                    'mail_host'         => ['required', 'string', 'max:150'],
                    'mail_encryption'   => ['required', 'string', 'max:20'],
                    'mail_port'         => ['required', 'string', 'max:20'],
                    'mail_driver'       => ['required', 'string', 'max:150'],
                    'company'           => ['required', 'not_in:0'],
                    'shop'              => ['required', 'string', 'max:150']
                ];
            }
            case 'PUT':
            {
                return [
                    'password'          => ['max:255'],
                    'customer_number'   => ['max:100'],
                    'api_key'           => ['max:255'],
                    'mail_password'     => ['required', 'string', 'max:255'],
                    'mail_username'     => ['required', 'string', 'max:100'],
                    'mail_from_name'    => ['required', 'string', 'max:150'],
                    'mail_from_address' => ['required', 'string', 'max:255'],
                    'mail_host'         => ['required', 'string', 'max:150'],
                    'mail_encryption'   => ['required', 'string', 'max:20'],
                    'mail_port'         => ['required', 'string', 'max:20'],
                    'mail_driver'       => ['required', 'string', 'max:150'],
                    'company'           => ['required', 'not_in:0'],
                    'shop'              => ['required', 'string', 'max:150']
                ];
            }
            default: break;
        }
    }
}
