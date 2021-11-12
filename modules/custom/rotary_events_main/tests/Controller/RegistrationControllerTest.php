<?php

namespace Drupal\rotary_events_main\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the rotary_events_main module.
 */
class RegistrationControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "rotary_events_main RegistrationController's controller functionality",
      'description' => 'Test Unit for module rotary_events_main and controller RegistrationController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests rotary_events_main functionality.
   */
  public function testRegistrationController() {
    // Check that the basic functions of module rotary_events_main.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
