<?php

declare(strict_types=1);

namespace App\Application;

use App\Exception\ConfigErrorException;
use Symfony\Component\Yaml\Yaml;

class ConfigApplication
{
    private const string CONFIG_APP_FILE = 'config/app/config.yml';
    private const string CONFIG_ASSTATS_FILE = 'asstats.yml';
    private string $environment;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

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

    public function isDev(): bool
    {
        if ('dev' === $this->environment) {
            return true;
        }

        return false;
    }

    public static function getRelease(): mixed
    {
        return self::getConfig()['application']['release'];
    }

    public static function getLocale(): mixed
    {
        return self::getConfig()['application']['locale'];
    }

    private static function getAsStatsConfig(): array
    {
        if (false === \array_key_exists('config', self::getConfigAsStats())) {
            throw new ConfigErrorException('Unable to found config variable');
        }

        return self::getConfigAsStats()['config'];
    }

    /**
     * @throws ConfigErrorException
     */
    public static function getAsStatsConfigTop(): int
    {
        if (false === \array_key_exists('top', self::getAsStatsConfig())) {
            throw new ConfigErrorException('Unable to found config.top variable');
        }

        return self::getAsStatsConfig()['top'];
    }

    /**
     * @throws ConfigErrorException
     */
    public static function getAsStatsConfigKnownLinksFile(): string
    {
        if (false === \array_key_exists('knownlinksfile', self::getAsStatsConfig())) {
            throw new ConfigErrorException('Unable to found config.knownlinksfile variable');
        }

        if (!self::getAsStatsConfig()['knownlinksfile']) {
            throw new ConfigErrorException('Unable to found config.knownlinksfile variable');
        }

        return self::getAsStatsConfig()['knownlinksfile'];
    }

    public static function getAsStatsConfigTopInterval(): array
    {
        if (false === \array_key_exists('top_intervals', self::getConfigAsStats())) {
            return [];
        }

        return self::getConfigAsStats()['top_intervals'];
    }

    /**
     * @throws ConfigErrorException
     */
    public static function getAsStatsConfigDayStatsFile(): string
    {
        if (false === \array_key_exists('daystatsfile', self::getAsStatsConfig())) {
            throw new ConfigErrorException('Unable to found config.daystatsfile variable');
        }

        return self::getAsStatsConfig()['daystatsfile'];
    }

    public static function getAsStatsConfigAsInfoFile(): string
    {
        if (false === \array_key_exists('asinfofile', self::getAsStatsConfig())) {
            throw new ConfigErrorException('Unable to found config.asinfofile variable');
        }

        return self::getRootPathApp().self::getAsStatsConfig()['asinfofile'];
    }
}
