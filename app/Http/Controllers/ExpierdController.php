<?php

namespace App\Http\Controllers;

use App\Organization;
use Illuminate\Http\Request;

class ExpierdController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    //
    public function index(){
//        return 'ok go out';
        return response()->view('errors.expierd');
//        return view('expierd' , compact('organization'));
    }
}
