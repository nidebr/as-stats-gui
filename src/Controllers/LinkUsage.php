<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Application\ConfigApplication as ConfigApplication;

/**
 * @SLX\Controller(prefix="/linkusage")
 */
class LinkUsage extends BaseController
{
  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="linkusage")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $this->data['active_page'] = $app['func']->getRouteName($request);
    $req = $request->query->all();

    $hours = 24;
    if (@$req['numhours']) $hours = (int)$req['numhours'];
    $this->data['label'] = $app['func']->statsLabelForHours($hours);
    $this->data['hours'] = $hours;
    $this->data['knownlinks'] = $app['func']->getKnowlinks();

    return $app['twig']->render('pages/linkusage/linkusage.html.twig', $this->data);
  }
}
