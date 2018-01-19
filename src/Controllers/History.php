<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SLX\Controller(prefix="/history")
 */
class History extends BaseController
{
  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="history")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $req = $request->query->all();
    $this->data['active_page'] = $app['func']->getRouteName($request);
    if ( isset($req['as']) ) {
      $this->data['request'] = $req;
      $this->data['asinfo'] = $app['func']->GetASInfo($req['as']);
      $this->data['customlinks'] = $app['func']->getCustomLinks_History($req['as']);

      $this->data['end'] = time();

      $this->data['daily']['start'] = time() - 24 * 3600;
      $this->data['weekly']['start'] = time() - 6.9 * 86400;
      $this->data['monthly']['start'] = time() - 30 * 86400;
      $this->data['yearly']['start'] = time() - 365 * 86400;

      return $app['twig']->render('pages/history/history.html.twig', $this->data);
    } else {
      return $app['twig']->render('pages/history/search.html.twig', $this->data);
    }
  }
}
