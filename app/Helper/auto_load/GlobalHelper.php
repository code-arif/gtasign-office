<?php

use Illuminate\Support\Str;

if (!function_exists('simple_slug')) {
    function simple_slug(string $name): string
    {
        return Str::slug($name) . '-' . Str::lower(Str::random(6));
    }
}