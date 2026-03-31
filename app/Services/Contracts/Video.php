<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface Video
{
    public function upload(Request $request, $proformaId);


    public function getUrl($videoId);


    public function delete($videoId);


    public function copyVideos(Request $request);
}
