<?php

namespace App\Http\Controllers;

class AccountController extends Controller
{
    public function profile()
    {
        return view('account.profile');
    }

    public function security()
    {
        return view('account.security');
    }

    public function api()
    {
        return view('account.api');
    }
}
