<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Application\ConfigApplication as ConfigApplication;
use Controllers\Func;

/**
 * @SLX\Controller(prefix="/asset")
 */
class Asset extends BaseController
{
  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="asset")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $req = $request->query->all();
    $this->data['active_page'] = Func::getRouteName($request);

    if ( isset($req['asset']) ) {
      $hours = 24;

      $this->data['asset'] = strtoupper($req['asset']);
      $whois = $app['whois']->getASSET($this->data['asset']);

      $this->data['cache'] = $whois['cache'];
      $this->data['aslist'] = $whois['aslist'];
      $this->data['other_asset'] = $whois['other_asset'];

      $this->data['knownlinks'] = Func::getKnowlinks();

      $selected_links = array();
      foreach($this->data['knownlinks'] as $link){
         if(isset($req["link_${link['tag']}"]))
           $selected_links[] = $link['tag'];
      }
      $this->data['selected_links'] = $selected_links;
      $this->data['request'] = $req;

      $topas = $this->db->GetASStatsTop(200, Func::statsFileForHours($hours), $selected_links, $this->data['aslist']);

      $this->data['asinfo'] = NULL;
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

      foreach ( $this->data['aslist'] as $as ) {
        if ( !array_key_exists($as, $topas) ) {
          $this->data['asinfo_nodata'][$as]['info'] = Func::GetASInfo($as);
          $this->data['customlinks'][$as] = Func::getCustomLinks($as);
        }
      }

      $this->data['start'] = time() - $hours*3600;
      $this->data['end'] = time();
      $this->data['label'] = Func::statsLabelForHours($hours);
      $this->data['hours'] = $hours;

      #$app['util']::var_dump($this->data);

      return $app['twig']->render('pages/asset/asset.html.twig', $this->data);
    } else {
      return $app['twig']->render('pages/asset/search.html.twig', $this->data);
    }
  }

  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/clear"),
     *      @SLX\Bind(routeName="asset/clear")
     * )
  */
  public function clear_all(Application $app)
  {
    $path = ConfigApplication::getASSetPath();
    $ok = 0;
    $fail = 0;

    $files = glob($path."/*.txt");
		foreach($files as $file) {
			if ( unlink($file) ) { $ok++; } else { $fail++; }
		}

    $return = [
      'ok'  => $ok,
      'fail'  => $fail,
    ];

    return json_encode($return);
  }

  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/clear/{file}"),
     *      @SLX\Bind(routeName="asset/clear/one")
     * )
  */
  public function clear_one(Application $app, $file)
  {
    if (!preg_match("/^[a-zA-Z0-9:_-]+$/", $file)) return FALSE;

    $file = ConfigApplication::getASSetPath() . "/" . $file . ".txt";
    unlink($file);
    return "";
  }
}
