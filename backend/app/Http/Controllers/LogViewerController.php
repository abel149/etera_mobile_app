<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
        public function index()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'superadmin']), 403);
        return view('admin.logs');
    }

    public function fetchLogs()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'superadmin']), 403);
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            return response()->json([
                'logs' => 'Log file not found.'
            ]);
        }

        $logs = File::get($path);

        return response()->json([
            'logs' => $logs
        ]);
    }
}
