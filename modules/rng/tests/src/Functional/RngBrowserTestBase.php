<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\rng\Traits\RngTestTrait;

/**
 * Base test class for functional browser tests.
 */
abstract class RngBrowserTestBase extends BrowserTestBase {

  use RngTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rng',
    'user',
    'field',
    'dynamic_entity_reference',
    'unlimited_number',
    'courier',
    'text',
  ];

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->eventManager = $this->container->get('rng.event_manager');
  }

}
