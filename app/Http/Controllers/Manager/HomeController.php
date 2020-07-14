<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:usuario_manager');
    }

    public function index()
    {
        $data['title'] = 'Portada';
        $data['content'] = view('manager.home', $data);

        return view('layouts.manager.app', $data);
    }
}
