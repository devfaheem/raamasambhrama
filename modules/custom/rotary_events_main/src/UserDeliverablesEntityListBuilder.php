<?php

namespace Drupal\rotary_events_main;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of User deliverables entity entities.
 *
 * @ingroup rotary_events_main
 */
class UserDeliverablesEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('User deliverables entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\rotary_events_main\Entity\UserDeliverablesEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.user_deliverables_entity.edit_form',
      ['user_deliverables_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
