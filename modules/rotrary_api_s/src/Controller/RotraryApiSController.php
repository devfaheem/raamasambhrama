<?php

namespace Drupal\rotrary_api_s\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Rotrary Api&#039;s routes.
 */
class RotraryApiSController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
