<?php

namespace Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Application\ConfigApplication as ConfigApplication;
use DDesrosiers\SilexAnnotations\AnnotationServiceProvider;
use \utilphp\util as Util;
use \Mobile_Detect;

class ApplicationProvider implements ServiceProviderInterface
{
  public function register(Container $app)
  {
    if ( ConfigApplication::getDebug() )
    {
      $app['debug'] = TRUE;
    } else {
      $app['debug'] = FALSE;
    }

    // LOCALE
    $app['locale'] = ConfigApplication::getLocale();
    $app['session.default_locale'] = $app['locale'];

    $app->register(new ServiceControllerServiceProvider());
    $app->register(new HttpFragmentServiceProvider());
    $app->register(new SessionServiceProvider());

    $app['mobile_detect'] = function($app) {
      return new Mobile_Detect();
    };

    $app['util'] = function($app) {
      return new Util();
    };

    $app->register(new DoctrineServiceProvider(), array(
      "dbs.options" => DbsProvider::Get()
    ));

    $app['table.sql'] = function() use($app) {
      return new \Models\SqlLite($app['dbs']);
    };

    $app['whois'] = function() use($app) {
      return new \Models\Whois($app);
    };

    $app->register(new AnnotationServiceProvider(), array(
      'annot.controllerDir' => realpath(ConfigApplication::getControllerRootDirectory()),
    ));

    $app->register(new ErrorProvider());
  }
}
