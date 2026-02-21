<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class UniqueCodeGenerator
{

    public static function generateCode(int $length = 0): string {
        return (string) Str::uuid();
    }

    public static function generateInitial(string $name = '',int $length = 0): string {
        if (strlen($name) == 0) {
            return UniqueCodeGenerator::generateRandom(6);
        }

        $result = preg_replace("/[^a-zA-Z0-9]+/", "", $name);

        return substr($result, 0, 3).UniqueCodeGenerator::generateRandom();
    }

    public static function generateSearchName(string $name): string
    {
        return strtolower($name);
    }

    public static function generateRandom(int $len = 3): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $len; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}