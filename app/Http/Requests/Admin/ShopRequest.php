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
                    'shop_password'          => ['max:255'],
                    'shop_customer_number'   => ['max:100'],
                    'shop_api_key'           => ['max:255'],
                    'shop_mail_password'     => ['required', 'string', 'max:255'],
                    'shop_mail_username'     => ['required', 'string', 'max:100'],
                    'shop_mail_from_name'    => ['required', 'string', 'max:150'],
                    'shop_mail_from_address' => ['required', 'string', 'email', 'max:255'],
                    'shop_mail_host'         => ['required', 'string', 'max:150'],
                    'shop_mail_encryption'   => ['required', 'string', 'max:20'],
                    'shop_mail_port'         => ['required', 'string', 'max:20'],
                    'shop_mail_driver'       => ['required', 'string', 'max:150'],
                    'shop_company'           => ['required', 'not_in:0'],
                    'shop_name'              => ['required', 'not_in:0']
                ];
            }
            case 'PUT':
            {
                return [
                    'shop_password'          => ['max:255'],
                    'shop_customer_number'   => ['max:100'],
                    'shop_api_key'           => ['max:255'],
                    'shop_mail_password'     => ['required', 'string', 'max:255'],
                    'shop_mail_username'     => ['required', 'string', 'max:100'],
                    'shop_mail_from_name'    => ['required', 'string', 'max:150'],
                    'shop_mail_from_address' => ['required', 'string', 'max:255'],
                    'shop_mail_host'         => ['required', 'string', 'max:150'],
                    'shop_mail_encryption'   => ['required', 'string', 'max:20'],
                    'shop_mail_port'         => ['required', 'string', 'max:20'],
                    'shop_mail_driver'       => ['required', 'string', 'max:150'],
                    'shop_company'           => ['required', 'not_in:0'],
                    'shop_name'              => ['required', 'not_in:0']
                ];
            }
            default: break;
        }
    }
}
