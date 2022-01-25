<?php

namespace App\Http\Controllers;

use App\Models\Sex;
use App\Models\Marital;
use Illuminate\Http\Request;

class MaestroController extends Controller
{

    public function indexSex()
    {
        return response()->json(['data' => Sex::all()]);
    }

    public function indexMarital()
    {
        return response()->json(['data' => Marital::all()]);
    }

}
