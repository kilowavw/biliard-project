<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LampuController extends Controller
{
    public function index()
    {
        $mejas = Meja::orderBy('nama_meja')->paginate(10);
        return view('lampu.index', compact('mejas'));
    }
}
