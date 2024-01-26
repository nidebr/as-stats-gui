<?php

declare(strict_types=1);

namespace App\Util;

class EndWithFunction
{
    public static function endsWith(string $FullStr, string $needle): bool
    {
        $StrLen = strlen($needle);
        $FullStrEnd = substr($FullStr, strlen($FullStr) - $StrLen);

        return $FullStrEnd === $needle;
    }
}
