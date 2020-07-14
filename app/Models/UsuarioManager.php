<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\UserManagerResetPasswordNotification;

class UsuarioManager extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario_manager';

    protected $table = 'usuario_manager';
    
    public $user_type = 'manager';

    protected $fillable = [
        'email',
    ];

     public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserManagerResetPasswordNotification($token));
    }
}
