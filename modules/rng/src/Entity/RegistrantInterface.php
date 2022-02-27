<?php

namespace Drupal\rng\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface defining a Registrant entity.
 */
interface RegistrantInterface extends ContentEntityInterface {

  /**
   * Gets associated registration.
   *
   * @return \Drupal\rng\Entity\RegistrationInterface|null
   *   The parent registration, or NULL if it does not exist.
   */
  public function getRegistration(): ?RegistrationInterface;

  /**
   * Sets associated registration.
   *
   * @param \Drupal\rng\Entity\RegistrationInterface $registration
   *   The new associated registration.
   *
   * @return $this
   */
  public function setRegistration(RegistrationInterface $registration): RegistrantInterface;

  /**
   * Gets associated content entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The parent event, if set.
   */
  public function getEvent(): ?ContentEntityInterface;

  /**
   * Sets the event for this registrant.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $event
   *   The content entity representing an event.
   *
   * @return $this
   */
  public function setEvent(ContentEntityInterface $event): RegistrantInterface;

  /**
   * Gets associated identity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   An entity, or NULL if the identity does not exist.
   */
  public function getIdentity(): ?EntityInterface;

  /**
   * Gets associated identity entity keys.
   *
   * @return array|null
   *   An array with the keys entity_type and entity_id, or NULL if the identity
   *   does not exist.
   */
  public function getIdentityId(): ?array;

  /**
   * Sets associated identity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The identity to set.
   *
   * @return $this
   */
  public function setIdentity(EntityInterface $entity): RegistrantInterface;

  /**
   * Removes identity associated with this registrant.
   *
   * @return $this
   */
  public function clearIdentity(): RegistrantInterface;

  /**
   * Checks if the identity is the registrant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The identity to check is associated with this registrant.
   *
   * @return bool
   *   Whether the identity is the registrant.
   */
  public function hasIdentity(EntityInterface $entity): bool;

  /**
   * Gets registrants belonging to an identity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $identity
   *   An identity entity.
   *
   * @return int[]
   *   An array of registrant entity IDs.
   */
  public static function getRegistrantsIdsForIdentity(EntityInterface $identity): array;

}
