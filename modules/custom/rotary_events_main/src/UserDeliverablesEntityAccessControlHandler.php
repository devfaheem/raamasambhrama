<?php

namespace Drupal\rotary_events_main;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the User deliverables entity entity.
 *
 * @see \Drupal\rotary_events_main\Entity\UserDeliverablesEntity.
 */
class UserDeliverablesEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\rotary_events_main\Entity\UserDeliverablesEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished user deliverables entity entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published user deliverables entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit user deliverables entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete user deliverables entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add user deliverables entity entities');
  }


}
