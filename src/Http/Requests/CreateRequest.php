<?php

namespace Tv2regionerne\StatamicEvents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return User::current()->can('view statamic events');
    }

    public function rules()
    {
        return [];
    }
}
