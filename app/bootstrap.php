<?php

use Silex\Provider\TwigServiceProvider;
use Application\ConfigApplication as ConfigApplication;
use Controllers\Func;

# TWIG -->
$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache'            => false,
        'strict_variables' => true,
    ),
    'twig.path'    => array(ConfigApplication::getTwigPathDirectory()),
    'twig.date.timezone' => ConfigApplication::getTwigTimezone(),
));

$app['twig'] = $app->extend("twig", function (\Twig_Environment $twig, Silex\Application $app) {
  $twig->addFilter( new Twig_SimpleFilter('var_dump', function ($stdClassObject) {
    return var_dump($stdClassObject);
  }));

  $twig->addFilter( new Twig_SimpleFilter('format_bytes', function ($stdClassObject) {
    return Func::format_bytes($stdClassObject);
  }));

  $twig->addFilter( new Twig_SimpleFilter('rawurlencode', function ($stdClassObject) {
    return rawurlencode($stdClassObject);
  }));

  $twig->addFilter( new Twig_SimpleFilter('concat_link', function ($stdClassObject) {
    $result = "";
    foreach($stdClassObject as $link) {
      $result .= "$link,";
    }
    return rtrim($result, ',');
  }));

  return $twig;
});

return $app;
