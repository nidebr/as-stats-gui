<?php

namespace Controllers;
use Application\ConfigApplication as ConfigApplication;

class BaseController
{
  protected $data;

  public function __construct()
  {
      global $prog, $app;
      $this->data['version'] = ConfigApplication::getRelease();
      $this->db = $app['table.sql'];
  }
}
