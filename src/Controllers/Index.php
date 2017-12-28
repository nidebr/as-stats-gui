<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Controllers\Func;

/**
 * @SLX\Controller(prefix="/")
 */
class Index extends BaseController
{
  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="index")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $topas = $this->db->GetASStatsTop('5','daystatsfile', array());

    foreach ($topas as $as => $nbytes) {
      $this->data['asinfo'][$as]['info'] = Func::GetASInfo($as);

      $this->data['asinfo'][$as]['v4'] = [
        'in' => $nbytes[0],
        'out' => $nbytes[1],
      ];

      $this->data['customlinks'][$as] = Func::getCustomLinks($as);
    }

    $this->data['active_page'] = Func::getRouteName($request);

    return $app['twig']->render('pages/index.html.twig', $this->data);
  }
}
