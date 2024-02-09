<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiscountCodes extends Controller
{
    public function checkCode(Request $request) {
        return response('Deez nuts');
    }
}
