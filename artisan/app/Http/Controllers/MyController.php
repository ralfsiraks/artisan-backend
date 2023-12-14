<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MyController extends Controller
{
    public function checkCode(Request $request, Response $response) {
        $code = $request->header('code');
        $response = DB::table('discount_codes')
            ->where('code', 'like', '%' . $code . '%')
            ->get();
        return response($response);
    }
}
