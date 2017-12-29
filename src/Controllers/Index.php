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
    $req = $request->query->all();

    if (isset($req['n'])) $ntop = (int)$req['n'];
    else $ntop = $this->data['config']['ntop'];
    if ($ntop > 200) $ntop = 200;

    $hours = 24;
    if (@$req['numhours']) $hours = (int)$req['numhours'];

    $topas = $this->db->GetASStatsTop($ntop,Func::statsFileForHours($hours), array());

    foreach ($topas as $as => $nbytes) {
      $this->data['asinfo'][$as]['info'] = Func::GetASInfo($as);

      $this->data['asinfo'][$as]['v4'] = [
        'in' => $nbytes[0],
        'out' => $nbytes[1],
      ];

      if ( $this->data['config']['showv6'] ) {
        $this->data['asinfo'][$as]['v6'] = [
          'in' => $nbytes[2],
          'out' => $nbytes[3],
        ];
      }

      $this->data['customlinks'][$as] = Func::getCustomLinks($as);
    }

    $this->data['active_page'] = Func::getRouteName($request);

    $this->data['start'] = time() - $hours*3600;
    $this->data['end'] = time();
    $this->data['ntop'] = $ntop;
    $this->data['label'] = Func::statsLabelForHours($hours);

    return $app['twig']->render('pages/index.html.twig', $this->data);
  }
}
