<?php

namespace Drupal\rotary_events_main\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining User deliverables entity entities.
 *
 * @ingroup rotary_events_main
 */
interface UserDeliverablesEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the User deliverables entity name.
   *
   * @return string
   *   Name of the User deliverables entity.
   */
  public function getName();

  /**
   * Sets the User deliverables entity name.
   *
   * @param string $name
   *   The User deliverables entity name.
   *
   * @return \Drupal\rotary_events_main\Entity\UserDeliverablesEntityInterface
   *   The called User deliverables entity entity.
   */
  public function setName($name);

  /**
   * Gets the User deliverables entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the User deliverables entity.
   */
  public function getCreatedTime();

  /**
   * Sets the User deliverables entity creation timestamp.
   *
   * @param int $timestamp
   *   The User deliverables entity creation timestamp.
   *
   * @return \Drupal\rotary_events_main\Entity\UserDeliverablesEntityInterface
   *   The called User deliverables entity entity.
   */
  public function setCreatedTime($timestamp);

}
