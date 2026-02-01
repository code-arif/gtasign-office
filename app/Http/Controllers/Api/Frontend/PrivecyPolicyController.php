<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PrivecyAndTerms;
use Illuminate\Http\Request;

class PrivecyPolicyController extends Controller
{
    /**
     * Display a listing of the privacy policies and terms.
     */
    public function privecyPolicy()
    {
       $data = PrivecyAndTerms::where('type', 'privacy')->first();
       return response()->json([
           'status' => true,
           'message' => 'Privacy policy fetched successfully',
           'data' => $data
       ]);
    }

    /**
     * Display a listing of the terms and conditions.
     */
    public function termsAndConditions()
    {
       $data = PrivecyAndTerms::where('type', 'terms')->first();
       return response()->json([
           'status' => true,
           'message' => 'Terms and conditions fetched successfully',
           'data' => $data
       ]);
    }
}
