<?php

namespace App\Models;

use App\Notifications\UserBackendResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario';

    protected $table = 'usuario';

    public $user_type = 'usuario_frontend';

    protected $fillable = [
        'email',
    ];
}
