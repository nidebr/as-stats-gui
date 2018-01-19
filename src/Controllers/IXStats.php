<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Application\ConfigApplication as ConfigApplication;

/**
 * @SLX\Controller(prefix="/ixstats")
 */
class IXStats extends BaseController
{
  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="ixstats")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $this->data['active_page'] = $app['func']->getRouteName($request);
    $req = $request->query->all();

    $hours = 24;
    if (@$req['numhours']) $hours = (int)$req['numhours'];

    $this->data['knownlinks'] = $app['func']->getKnowlinks();
    $selected_links = array();
    foreach($this->data['knownlinks'] as $link){
       if(isset($req["link_${link['tag']}"]))
         $selected_links[] = $link['tag'];
    }
    $this->data['selected_links'] = $selected_links;
    $this->data['hours'] = $hours;

    if ( $this->data['config']['my_asn'] ) {
      $this->data['myix'] = $app['peeringdb']->GetIX('34863');
    }

    if ( isset($req['ix']) ) {
      if (isset($req['n'])) $ntop = (int)$req['n'];
      else $ntop = $this->data['config']['ntop'];
      if ($ntop > 200) $ntop = 200;

      $this->data['request'] = $req;

      $list_asn = $app['peeringdb']->GetIXASN($req['ix']);
      $topas = $this->db->GetASStatsTop($ntop, $app['func']->statsFileForHours($hours), $selected_links, $list_asn);

      $this->data['asinfo'] = NULL;
      foreach ($topas as $as => $nbytes) {
        $this->data['asinfo'][$as]['info'] = $app['func']->GetASInfo($as);

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

        $this->data['customlinks'][$as] = $app['func']->getCustomLinks($as);
      }

      $this->data['start'] = time() - $hours*3600;
      $this->data['end'] = time();
      $this->data['ntop'] = $ntop;
      $this->data['label'] = $app['func']->statsLabelForHours($hours);
      return $app['twig']->render('pages/ixstats/ixstats.html.twig', $this->data);
    }

    return $app['twig']->render('pages/ixstats/search.html.twig', $this->data);
  }

  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="get_ixname"),
     *      @SLX\Bind(routeName="get_ixname")
     * )
  */
  public function get_ixname(Request $request, Application $app)
  {
    $req = $request->query->all();
    $return = NULL;

    if ( isset($req['name']) ) {
      foreach ($app['peeringdb']->GetIXName($req['name']) as $key => $value) {
        $return[] = array (
            'id' => strval($value->id),
            'name' => strval($value->name) . " (".$value->city." / ".$value->country.")",
        );
      }
    }

    return json_encode($return);
  }
}
