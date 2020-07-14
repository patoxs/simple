<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Doctrine_Query;

class CheckDocument implements Rule
{
    public $request;

    public $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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
        $id = $this->request->input('id');
        $key = $this->request->input('key');
        $key = preg_replace('/\W/', '', $key);

        $file = Doctrine_Query::create()
            ->from('File f')
            ->where('f.id = ?', $id)
            ->fetchOne();

        if (!$file) {
            $this->message = 'Folio y/o código no válido.';
            return false;
        }


        if ($file->llave_copia != $key) {
            $this->message = 'Folio y/o código no válido.';
            return false;
        }

        if ($file->validez !== null) {
            if ($file->validez_habiles) {
                $fecha_creacion = \Carbon\Carbon::parse($file->created_at);
                $fecha_expiracion = (new \App\Helpers\dateHelper())->add_working_days($fecha_creacion,$file->validez);
            } else {
                $fecha_creacion = \Carbon\Carbon::parse($file->created_at);
                $fecha_expiracion = $fecha_creacion->addDays($file->validez);
            }

            $fecha_actual = \Carbon\Carbon::today();
            if ($fecha_actual->greaterThan($fecha_expiracion) && $file->validez > 0) {
                $this->message = 'Documento expiró su periodo de validez.';
                return false;
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
        return $this->message;
    }
}
