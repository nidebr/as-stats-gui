<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Controllers\Func;

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
    $this->data['active_page'] = Func::getRouteName($request);
    if ( isset($req['as']) ) {
      $this->data['request'] = $req;
      $this->data['asinfo'] = Func::GetASInfo($req['as']);
      $this->data['customlinks'] = Func::getCustomLinks_History($req['as']);

      $this->data['daily']['start'] = time() - 24 * 3600;
      $this->data['daily']['end'] = time();

      $this->data['weekly']['start'] = time() - 6.9 * 86400;
      $this->data['weekly']['end'] = time();

      $this->data['monthly']['start'] = time() - 30 * 86400;
      $this->data['monthly']['end'] = time();

      $this->data['yearly']['start'] = time() - 365 * 86400;
      $this->data['yearly']['end'] = time();


      return $app['twig']->render('pages/history.html.twig', $this->data);
    } else {
      return $app['twig']->render('pages/history_search.html.twig', $this->data);
    }
  }
}
