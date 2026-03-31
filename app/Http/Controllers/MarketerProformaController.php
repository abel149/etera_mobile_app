<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proforma;

class MarketerProformaController extends Controller
{
    public function index()
     {
        // Load all published proformas
        $proformas = Proforma::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        // Components for filter buttons
        $components = ['Both', 'Body Parts', 'Mechanical Parts'];

        return view('marketer.proformas', compact('proformas', 'components'));
    }
}
