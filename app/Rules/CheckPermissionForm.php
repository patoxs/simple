<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Doctrine;

class CheckPermissionForm implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $formulario = Doctrine::getTable('Formulario')->find($value);

        return ($formulario->Proceso->cuenta_id == Auth::user()->cuenta_id);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Usuario no tiene permisos para agregar campos a este formulario.';
    }
}
