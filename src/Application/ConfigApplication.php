<?php

namespace Application;
use Symfony\Component\Yaml\Yaml;
use Silex\Application;

class ConfigApplication
{
    const PATH_CONFIG = __DIR__."/../../app/config/config.yml";

    public static function getRootPathApp()
    {
      return __DIR__.'/../../';
    }

    public static function getConfig()
    {
      $input = file_get_contents(self::PATH_CONFIG);
      $input = Yaml::parse($input);
      return $input;
    }

    public static function getRelease()
    {
      return self::getConfig()['application']['release'];
    }

    public static function getDebug()
    {
      return self::getConfig()['application']['debug'];
    }

    public static function getControllerRootDirectory()
    {
      return self::getRootPathApp().''.self::getConfig()['application']['controller'];
    }

    public static function getTwigPathDirectory()
    {
      return self::getRootPathApp().''.self::getConfig()['application']['twig']['path'];
    }

    public static function getTwigTimezone()
    {
      return self::getConfig()['application']['twig']['timezone'];
    }

    public static function getLocale()
    {
      return self::getConfig()['application']['locale'];
    }

    public static function getASStatsFilePath()
    {
      return self::getRootPathApp().''.self::getConfig()['application']['asstats_file'];
    }

    public static function getConfigASStats()
    {
      $input_yaml = file_get_contents(self::getASStatsFilePath());
      $output = Yaml::parse($input_yaml);
      return $output;
    }

    public static function getDbFile()
    {
      return self::getConfigASStats()['db'];
    }

    public static function getKnowlinksFile()
    {
      return self::getConfigASStats()['config']['knownlinksfile'];
    }

    public static function getASInfoFile()
    {
      return self::getRootPathApp().''.self::getConfigASStats()['config']['asinfofile'];
    }

    public static function getCustomLinks()
    {
      return self::getConfigASStats()['customlinks'];
    }

    public static function getTopInterval()
    {
      if ( !empty(self::getConfigASStats()['topinterval']) ) {
        return self::getConfigASStats()['topinterval'];
      } else {
        return FALSE;
      }
    }

    public static function getASStatsAllConfig()
    {
      return self::getConfigASStats()['config'];
    }

    public static function getMobile(Application $app)
    {
      return !$app['mobile_detect']->isMobile() && !$app['mobile_detect']->isTablet() ? FALSE : TRUE;
    }

    public static function getASSetPath()
    {
      return self::getConfigASStats()['config']['assetpath'];
    }

    public static function getASSetCacheLife()
    {
      return self::getConfigASStats()['config']['asset_cache_life'];
    }

    public static function getWhois()
    {
      return self::getConfigASStats()['config']['whois'];
    }

    public static function getUrlPeeringDB()
    {
      return self::getConfigASStats()['api']['peeringdb']['host'].''.self::getConfigASStats()['api']['peeringdb']['url'];
    }
}
