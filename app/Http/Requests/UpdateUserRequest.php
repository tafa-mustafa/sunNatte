<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        //abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            
        'nom' => 'nullable|string|max:255',
        'prenom' => 'nullable|string',
        'profession' => 'nullable|string',
        'adresse' => 'nullable|string',
        'num_cni' => 'nullable|numeric',
        'num_passport' => 'nullable|string',
        'bank' => 'nullable|string',
        'phone2' => 'nullable|numeric',
        'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'preuve_fond' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}