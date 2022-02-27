<?php

namespace Drupal\Tests\rng\Traits;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\rng\Entity\EventTypeRule;
use Drupal\rng\Entity\RegistrationTypeInterface;
use Drupal\rng\Entity\Registration;
use Drupal\rng\Entity\RegistrationType;
use Drupal\rng\Entity\RngEventType;
use Drupal\rng\EventManagerInterface;

/**
 * Provides methods useful for Kernel and Functional RNG tests.
 *
 * This trait is meant to be used only by test classes.
 */
trait RngTestTrait {

  /**
   * Creates and save a registration type entity.
   *
   * @return \Drupal\rng\Entity\RegistrationTypeInterface
   *   A registration type entity.
   */
  protected function createRegistrationType() {
    $registration_type = RegistrationType::create([
      'id' => 'registration_type_a',
      'label' => 'Registration Type A',
      'description' => 'Description for registration type a',
    ]);
    $registration_type->save();
    return $registration_type;
  }

  /**
   * Creates an event type config.
   *
   * @param string $entity_type_id
   *   An entity type ID.
   * @param string $bundle
   *   An entity type bundle.
   * @param array $values
   *   Optional values for the event type.
   *
   * @return \Drupal\rng\Entity\EventTypeInterface
   *   An event type config.
   */
  protected function createEventType($entity_type_id, $bundle, array $values = []) {
    $event_type = RngEventType::create($values + [
      'label' => 'Event Type A',
      'entity_type' => $entity_type_id,
      'bundle' => $bundle,
      'mirror_operation_to_event_manage' => 'update',
    ]);
    $event_type->setIdentityTypeReference('user', 'user', TRUE);
    $event_type->setDefaultRegistrantType('registrant');
    $event_type->save();
    return $event_type;
  }

  /**
   * Creates an event and returns an EventMetaInterface object.
   *
   * @return \Drupal\rng\EventMetaInterface
   *   A meta event wrapper.
   */
  protected function createEventMeta($values = []) {
    $event = EntityTest::create($values + [
      EventManagerInterface::FIELD_REGISTRATION_TYPE => $this->registrationType->id(),
      EventManagerInterface::FIELD_STATUS => TRUE,
      EventManagerInterface::FIELD_ALLOW_DUPLICATE_REGISTRANTS => 0,
    ]);
    $event->save();
    return $this->eventManager->getMeta($event);
  }

  /**
   * Creates an event node.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity_type
   *   An entity type.
   * @param array $settings
   *   Additional settings for the new entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An event.
   */
  public function createEventNode(ConfigEntityInterface $entity_type, array $settings = []) {
    $entity = $this->drupalCreateNode([
      'type' => $entity_type->id(),
    ] + $settings);
    return $entity;
  }

  /**
   * Creates a registration and add an identity as a registrant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   An event entity.
   * @param \Drupal\rng\Entity\RegistrationTypeInterface $registration_type
   *   A registration type.
   * @param \Drupal\Core\Entity\EntityInterface[] $identities
   *   An array of identities.
   * @param array $values
   *   (optional) The values to pass to the registration.
   *
   * @return \Drupal\rng\Entity\RegistrationInterface
   *   A saved registration.
   */
  protected function createRegistration(EntityInterface $event, RegistrationTypeInterface $registration_type, array $identities, array $values = []) {
    $registration = Registration::create($values + [
      'type' => $registration_type->id(),
    ]);
    $registration->setEvent($event);
    foreach ($identities as $identity) {
      $registration->addIdentity($identity);
    }
    $registration->save();
    return $registration;
  }

  /**
   * Creates rules for an event type.
   *
   * @param array $roles
   *   An array of role ID to add access.
   * @param array $operations
   *   An array of operations. Value is boolean whether to grant, key can be
   *   any of 'create', 'view', 'update', 'delete'.
   */
  protected function createUserRoleRules(array $roles = [], array $operations = []) {
    $rule = EventTypeRule::create([
      'trigger' => 'rng_event.register',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'machine_name' => 'user_role',
    ]);
    $rule->setCondition('role', [
      'id' => 'rng_user_role',
      'roles' => $roles,
    ]);
    $rule->setAction('registration_operations', [
      'id' => 'registration_operations',
      'configuration' => [
        'operations' => $operations,
      ],
    ]);
    $rule->save();
  }

}
