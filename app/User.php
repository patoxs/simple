<?php

namespace App;

use App\Notifications\UserFrontResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * @var string
     */
    protected $table = 'usuario';
    
    public $user_type = 'frontend';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usuario', 'salt', 'nombres', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function grupo_usuarios()
    {
        return $this->belongsToMany('App\Models\GrupoUsuarios', 'grupo_usuarios_has_usuario', 'usuario_id', 'grupo_usuarios_id');
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserFrontResetPasswordNotification($token));
    }
}
