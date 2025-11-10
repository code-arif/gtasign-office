<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CMS;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // $about_us = CMS::where('page', 'home')
        //     ->where('section', 'about_us')
        //     ->first();

        // $about_us_sections = CMS::where('page', 'home')
        //     ->where('section', 'about_uss')
        //     ->get();




        // $order = CMS::where('page', 'home')
        //     ->where('section', 'order_and_delivery_content')
        //     ->first();

        // $order_items = CMS::where('page', 'home')
        //     ->where('section', 'order_and_delivery_items')
        //     ->get();


        // $banners = CMS::where('page', 'home')
        //     ->where('section', 'home_banners')
        //     ->get();


        // return view('website.layouts.index', compact('about_us', 'about_us_sections', 'order', 'order_items', 'banners'));

        return view('welcome');
    }
}
