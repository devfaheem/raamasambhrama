<?php

/**
 * @file
 * Contains user_deliverables_entity.page.inc.
 *
 * Page callback for User deliverables entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for User deliverables entity templates.
 *
 * Default template: user_deliverables_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_deliverables_entity(array &$variables) {
  // Fetch UserDeliverablesEntity Entity Object.
  $user_deliverables_entity = $variables['elements']['#user_deliverables_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
