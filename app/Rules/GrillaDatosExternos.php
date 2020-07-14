<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class GrillaDatosExternos implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    private  $validations;

    public function __construct( $validations )
    {
        $this->validations = $validations;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $grilla = json_decode($value, true);

        if( empty($grilla) ) {
          return true;
        }

        foreach ($grilla as $row) {
          foreach ($row as $key => $value ) {
              $rules = $this->validations[$key];
              $validator = Validator::make( [$attribute => $value] , [$attribute => $rules] );
              if ($validator->fails()) {
                  return false;
              }
          }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.valid_grilla');
    }
}
