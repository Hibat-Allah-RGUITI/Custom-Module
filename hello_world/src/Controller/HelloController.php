<?php

namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;

class HelloController extends ControllerBase
{
  public function content()
  {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello World'),
    ];
  }

  public function systemAdminMenuBlockPage()
  {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello World Admin Page'),
    ];
  }
}
