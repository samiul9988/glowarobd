<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;

class FirebaseMessageController extends Controller
{
    public function index()
    {
        return view('firebase-message.index');
    }
}
