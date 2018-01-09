<?php

namespace Controllers;
use Application\ConfigApplication as ConfigApplication;

class BaseController
{
  protected $data;

  public function __construct()
  {
      global $app;
      $this->data['version'] = ConfigApplication::getRelease();
      $this->db = $app['table.sql'];

      // Top Interval
      $topinterval = ConfigApplication::getTopInterval();
      if ( $topinterval ) {
        foreach ($topinterval as $interval) {
          $this->data['topinterval'][] = $interval;
        }
      }

      $this->data['config'] = ConfigApplication::getASStatsAllConfig();
      $this->data['mobile'] = ConfigApplication::getMobile($app);
  }
}
