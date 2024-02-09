<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountCodes extends Controller
{
    public function checkCode(Request $request) {
        $code = $request->header('code');
        $response = DB::table('discount_codes')
            ->where('code',  $code)
            ->get();
        return response()->json($response);
    }
}
