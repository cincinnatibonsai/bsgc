<?php

namespace Drupal\Tests\rng\Kernel;

use Drupal\rng\Entity\Registration;
use Drupal\rng\EventManagerInterface;

/**
 * Tests registration types.
 *
 * @group rng
 */
class RngRegistrationTypeTest extends RngKernelTestBase {

  /**
   * A registration type configuration entity.
   *
   * @var \Drupal\rng\Entity\RegistrationTypeInterface
   */
  protected $registrationType;

  /**
   * An event type for testing.
   *
   * @var \Drupal\rng\Entity\EventTypeInterface
   */
  protected $eventType;

  /**
   * An event.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $event;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->registrationType = $this->createRegistrationType();
    $this->eventType = $this->setupEventType();
    $this->event = $this->createEventMeta()->getEvent();
  }

  /**
   * Test registration type deletion.
   */
  public function testRegistrationTypeApiDelete() {
    $this->assertEquals(1, $this->countEventRegistrationTypeReferences($this->event->getEntityTypeId(), $this->registrationType->id()), 'One reference exists to this registration type');

    $registration[0] = $this->createRegistration($this->event, $this->registrationType, []);
    $this->registrationType->delete();

    // Clear storage cache.
    $this->container->get('entity_type.manager')->getStorage('registration')->resetCache();

    $this->assertCount(0, Registration::loadMultiple(), 'Registrations no longer exist');
    $this->assertEquals(0, $this->countEventRegistrationTypeReferences($this->event->getEntityTypeId(), $this->registrationType->id()), 'No references from event entities to this registration type');
  }

  /**
   * Count references from event entities to registration types.
   *
   * @param string $entity_type
   *   An entity type ID.
   * @param string $registration_type
   *   A registration type ID.
   *
   * @return int
   *   Number of references.
   */
  public function countEventRegistrationTypeReferences($entity_type, $registration_type) {
    return \Drupal::entityTypeManager()->getStorage($entity_type)
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition(EventManagerInterface::FIELD_REGISTRATION_TYPE, $registration_type)
      ->count()
      ->execute();
  }

}
