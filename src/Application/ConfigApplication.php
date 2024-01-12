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

    private static function getConfig(): array
    {
        $input = file_get_contents(self::getRootPathApp().self::CONFIG_APP_FILE);
        return (array) Yaml::parse((string) $input);
    }

    private static function getConfigAsStats(): array
    {
        $input = file_get_contents(self::getRootPathApp().self::CONFIG_ASSTATS_FILE);
        return (array) Yaml::parse((string) $input);
    }

    public static function getRelease(): mixed
    {
        return self::getConfig()['application']['release'];
    }

    public static function getLocale(): mixed
    {
        return self::getConfig()['application']['locale'];
    }

    public static function getAsStatsTopInterval(): mixed
    {
        if (false === \array_key_exists('top_intervals', self::getConfigAsStats())) {
            return false;
        }

        return self::getConfigAsStats()['top_intervals'];
    }

    public static function getAsStatsConfig(): mixed
    {
        if (false === \array_key_exists('config', self::getConfigAsStats())) {
            return false;
        }

        return self::getConfigAsStats()['config'];
    }
}
