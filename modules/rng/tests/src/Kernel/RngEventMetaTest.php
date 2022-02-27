<?php

namespace Drupal\Tests\rng\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\EventMetaInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Tests the event meta class.
 *
 * @group rng
 * @coversDefaultClass \Drupal\rng\EventMeta
 */
class RngEventMetaTest extends RngKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'entity_test'];

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * An event type for testing.
   *
   * @var \Drupal\rng\Entity\EventTypeInterface
   */
  protected $eventType;

  /**
   * Constant representing unlimited.
   *
   * @var \Drupal\rng\EventMetaInterfaceCAPACITY_UNLIMITED
   */
  protected $unlimited;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->eventManager = $this->container->get('rng.event_manager');
    $this->eventType = $this->setupEventType();
    $this->unlimited = EventMetaInterface::CAPACITY_UNLIMITED;
  }

  /**
   * Tests maximum registrants is unlimited if there is no field value.
   *
   * Including no default field value on the entity level.
   *
   * @covers ::getRegistrantsMaximum
   */
  public function testRegistrantsMaximumNoField() {
    $field = FieldConfig::loadByName('entity_test', 'entity_test', EventManagerInterface::FIELD_REGISTRANTS_CAPACITY);
    $field->delete();

    $event = EntityTest::create();
    $event_meta = $this->eventManager->getMeta($event);
    $this->assertSame($this->unlimited, $event_meta->getRegistrantsMaximum(), 'Maximum registrants is unlimited when no field exists.');
  }

  /**
   * Tests maximum registrants is unlimited if there is no field value.
   *
   * @covers ::getRegistrantsMaximum
   */
  public function testRegistrantsMaximumDefaultValue() {
    $field = FieldConfig::loadByName('entity_test', 'entity_test', EventManagerInterface::FIELD_REGISTRANTS_CAPACITY);
    $field
      ->setDefaultValue([['value' => 666]])
      ->save();

    $event = EntityTest::create();
    $event_meta = $this->eventManager->getMeta($event);
    $this->assertSame(666, $event_meta->getRegistrantsMaximum(), 'Maximum registrants matches bundle default value.');
  }

  /**
   * Tests maximum registrants is unlimited if there is no field value.
   *
   * @covers ::getRegistrantsMaximum
   */
  public function testRegistrantsMaximumNoDefaultValue() {
    $event = EntityTest::create();
    $event_meta = $this->eventManager->getMeta($event);
    $this->assertSame($this->unlimited, $event_meta->getRegistrantsMaximum(), 'Maximum registrants matches empty bundle default.');
  }

}
