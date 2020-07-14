<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';
    
    public static $finished = 'finished';
    public static $running = 'running';
    public static $error = 'error';
    public static $created = 'created';
}
