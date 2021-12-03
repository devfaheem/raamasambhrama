<?php

namespace Drupal\rotary_events_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;

/**
 * Provides a 'HeaderMenuBlock' block.
 *
 * @Block(
 *  id = "header_menu_block",
 *  admin_label = @Translation("Header menu block"),
 * )
 */
class HeaderMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'header_menu_block';
     $build['header_menu_block']['#markup'] = 'Implement HeaderMenuBlock.';
     $logged_in = \Drupal::currentUser()->isAuthenticated();
     $build['#isLoggedIn'] = $logged_in;
     $build['#status'] = "";
    if($logged_in){
      $account = User::load(\Drupal::currentUser()->id());
      $build['#status'] = $account->get("field_payment_status")->value;
    }
     

    return $build;
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
