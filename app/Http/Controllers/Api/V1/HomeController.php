<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, HomeService $home)
    {
        return response()->json($home->build($request->user()));
    }
}
