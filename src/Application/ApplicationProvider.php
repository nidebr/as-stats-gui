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

    $app->register(new DoctrineServiceProvider(), array(
      "dbs.options" => DbsProvider::Get()
    ));

    $app['table.sql'] = function() use($app) {
      return new \Models\SqlLite($app['dbs']);
    };

    $app->register(new AnnotationServiceProvider(), array(
      'annot.controllerDir' => realpath(ConfigApplication::getControllerRootDirectory()),
    ));

    $app->register(new ErrorProvider());
  }
}
