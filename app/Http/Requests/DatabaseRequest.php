<?php

namespace App\Http\Requests;

use App\Installation\Installer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DatabaseRequest extends FormRequest
{
    protected $redirect = '/install/database';

    public function authorize(): bool
    {
        return true; // Deployment access middleware precedes all installer Form Requests.
    }

    protected function prepareForValidation(): void
    {
        app(Installer::class)->invalidate();
    }

    public function rules(): array
    {
        return [
            'db_host' => ['required', 'string', Rule::in(config('installation.database_hosts'))],
            'db_port' => ['required', 'integer', Rule::in([config('installation.database_port')])],
            'db_name' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_]{1,64}\z/'],
            'db_user' => ['required', 'string', 'max:128', 'not_regex:/[\x00-\x1F]/'],
            'db_password' => ['present', 'nullable', 'string', 'max:1024'],
        ];
    }
}
