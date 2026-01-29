<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Str;

if (!function_exists('generate_username')) {
    function generate_username(string $Name): string
{
    $baseUsername = Str::slug(trim($Name), '_');
    $username = $baseUsername;

    while (Profile::where('username', $username)->exists()) {
        $random = Str::upper(Str::random(4));
        $username = $baseUsername . $random;
    }

    return $username;
}
}

if (!function_exists('check_username')) {
    function check_username(string $username): string
{
    if (Profile::where('username', $username)->exists()) {
        return false;
    }
    return true;
}

}