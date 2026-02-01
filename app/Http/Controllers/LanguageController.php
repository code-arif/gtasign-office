<?php

namespace App\Http\Controllers;

use App\Http\Resources\LangResource;
use App\Models\Language;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    use ApiResponse;
    public function getLangApi()
    {
        $lang = Language::all();
       return $this->success(LangResource::collection($lang), 'All languages list');
    }
}
