<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class OpenController extends Controller
{
    //
    public function get_access_token(Request $request)
    {
        $jsonData = $request->input();
        print_r(Route::current()->uri());
    }
}
