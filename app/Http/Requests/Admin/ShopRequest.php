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
                    'shop'              => ['required', 'string', 'max:150'],
                    'company'           => ['required', 'not_in:0'],
                    'mail_driver'       => ['required', 'string', 'max:150'],
                    'mail_port'         => ['required', 'string', 'max:20'],
                    'mail_encryption'   => ['required', 'string', 'max:20'],
                    'mail_host'         => ['required', 'string', 'max:150'],
                    'mail_from_address' => ['required', 'string', 'max:255'],
                    'mail_from_name'    => ['required', 'string', 'max:150'],
                    'mail_username'     => ['required', 'string', 'max:100'],
                    'mail_password'     => ['required', 'string', 'max:255'],
                    'api_key'           => ['string', 'max:255'],
                    'customer_number'   => ['string', 'max:100'],
                    'password'          => ['string', 'max:255']
                ];
            }
            case 'PUT':
            {
                return [
                    'shop'              => ['required', 'string', 'max:150'],
                    'company'           => ['required', 'not_in:0'],
                    'mail_driver'       => ['required', 'string', 'max:150'],
                    'mail_port'         => ['required', 'string', 'max:20'],
                    'mail_encryption'   => ['required', 'string', 'max:20'],
                    'mail_host'         => ['required', 'string', 'max:150'],
                    'mail_from_address' => ['required', 'string', 'max:255'],
                    'mail_from_name'    => ['required', 'string', 'max:150'],
                    'mail_username'     => ['required', 'string', 'max:100'],
                    'mail_password'     => ['required', 'string', 'max:255'],
                    'api_key'           => ['string', 'max:255'],
                    'customer_number'   => ['string', 'max:100'],
                    'password'          => ['string', 'max:255']
                ];
            }
            default: break;
        }
    }
}
