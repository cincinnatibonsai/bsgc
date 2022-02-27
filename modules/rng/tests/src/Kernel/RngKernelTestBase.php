<?php

namespace Drupal\Tests\rng\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\rng\Traits\RngTestTrait;

/**
 * Base class for RNG unit tests.
 */
abstract class RngKernelTestBase extends KernelTestBase {

  use RngTestTrait;

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
    'entity_test',
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
    $this->installEntitySchema('courier_email');
    $this->installEntitySchema('courier_message_queue_item');
    $this->installEntitySchema('courier_template_collection');
    $this->installEntitySchema('registrant');
    $this->installEntitySchema('registration');
    $this->installEntitySchema('registration_group');
    $this->installConfig('rng');
  }

  /**
   * Installs rules related content entity types.
   */
  protected function setupRngRules() {
    $this->installEntitySchema('rng_rule');
    $this->installEntitySchema('rng_rule_scheduler');
    $this->installEntitySchema('rng_rule_component');
  }

  /**
   * Installs entity_test content type and creates an event type.
   *
   * @return \Drupal\rng\Entity\EventTypeInterface
   *   An event type config.
   */
  protected function setupEventType() {
    $this->installEntitySchema('entity_test');
    $event_type = $this->createEventType('entity_test', 'entity_test');
    return $event_type;
  }

}
