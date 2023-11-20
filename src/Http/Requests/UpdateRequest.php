<?php

namespace Tv2regionerne\StatamicEvents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return User::current()->can('edit statamic events');
    }

    public function rules()
    {
        return [];
    }
}
