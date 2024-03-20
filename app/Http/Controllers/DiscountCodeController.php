<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;

class DiscountCodeController extends Controller
{
    public function checkCode(Request $request) {
        $code = $request->header('code');
        $response = DiscountCode::where('code', $code)->get();
        return response()->json($response);
    }
}
