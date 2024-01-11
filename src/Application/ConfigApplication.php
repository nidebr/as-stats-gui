<?php

declare(strict_types=1);

namespace App\Application;

use Symfony\Component\Yaml\Yaml;

class ConfigApplication
{
    private const string CONFIG_APP_FILE = 'config/app/config.yml';
    private const string CONFIG_ASSTATS_FILE = 'asstats.yml';

    private static function getRootPathApp(): string
    {
        return \sprintf('%s/../../', __DIR__);
    }

    private static function getConfig(): mixed
    {
        $input = file_get_contents(self::getRootPathApp().self::CONFIG_APP_FILE);
        return Yaml::parse((string) $input);
    }

    public static function getRelease(): mixed
    {
        return self::getConfig()['application']['release'];
    }
}
