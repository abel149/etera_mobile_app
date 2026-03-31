<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface Audio
{
    public function upload(Request $request, $proformaId);


    public function getUrl($audioId);


    public function delete($audioId);


    public function copyAudios(Request $request);
}
