<?php

namespace App\Models;

use App\Notifications\UserBackendResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioBackend extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario_backend';

    protected $table = 'usuario_backend';

    public $user_type = 'backend';

    protected $fillable = [
        'email',
    ];

    public function Cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserBackendResetPasswordNotification($token));
    }

    public function isSuper()
    {
        return in_array('super', explode(',', $this->rol));
    }

    public function isModelamiento()
    {
        return in_array('modelamiento', explode(',', $this->rol));
    }

    public function isDesarrollo()
    {
        return in_array('desarrollo', explode(',', $this->rol));
    }

    public function isSeguimiento()
    {
        return in_array('seguimiento', explode(',', $this->rol));
    }

    public function isConfiguracion()
    {
        return in_array('configuracion', explode(',', $this->rol));
    }

    public function isGestion()
    {
        return in_array('gestion', explode(',', $this->rol));
    }

    public function isAgenda()
    {
        return in_array('agenda', explode(',', $this->rol));
    }

    public function isOperacion()
    {
        return in_array('operacion', explode(',', $this->rol));
    }

    public function historialModificaciones()
    {
        return $this->hasMany(HistorialModificacion::class, 'usuario_id');
    }
}
