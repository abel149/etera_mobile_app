<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
        public function index()
    {
        return view('admin.logs');
    }

    public function fetchLogs()
    {
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
