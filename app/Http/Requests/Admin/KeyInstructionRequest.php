<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KeyInstructionRequest extends FormRequest
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
                    'key_instruction_file' => ['file'],
                    'key_instruction_language' => ['required', 'not_in:0']
                ]; 
            }
            case 'PUT':
            {
                return [
                    'key_instruction_file' => ['required'],
                    'key_instruction_language' => ['required', 'not_in:0']
                ]; 
            }
            default:break;        
        }
    }
}
