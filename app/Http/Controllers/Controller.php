<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <--- PASTIKAN IMPORT INI ADA
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
     use AuthorizesRequests, ValidatesRequests;
}
