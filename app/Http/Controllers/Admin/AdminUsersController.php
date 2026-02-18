<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminUsersController extends Controller
{
    public function index()
    {
        // La data se carga vía API (fetch) para mantener un solo backend (SP-first).
        return view('admin.users.index');
    }
}
