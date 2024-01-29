<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

class GetJsonParameters
{
    public static function getAll(Request $request): array
    {
        if (\str_starts_with(\sprintf('%s', $request->headers->get('Content-Type')), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : []);
        }

        return $request->request->all();
    }
}
