<?php

namespace Drupal\rotary_events_main\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for User deliverables entity entities.
 */
class UserDeliverablesEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
