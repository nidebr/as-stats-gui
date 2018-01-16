<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Application\ConfigApplication as ConfigApplication;
use Controllers\Func;

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
    $this->data['active_page'] = Func::getRouteName($request);
    $req = $request->query->all();

    $hours = 24;
    if (@$req['numhours']) $hours = (int)$req['numhours'];
    $this->data['label'] = Func::statsLabelForHours($hours);
    $this->data['hours'] = $hours;
    $this->data['knownlinks'] = Func::getKnowlinks();

    return $app['twig']->render('pages/linkusage/linkusage.html.twig', $this->data);
  }
}
