<?php

namespace Drupal\rng\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\rng\EventMetaInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a Registration entity.
 */
interface RegistrationInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets associated event.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   An entity, or NULL if the event does not exist.
   */
  public function getEvent(): ?ContentEntityInterface;

  /**
   * Gets the event meta object for this event.
   *
   * @return \Drupal\rng\EventMetaInterface
   *   The event meta entity.
   */
  public function getEventMeta(): EventMetaInterface;

  /**
   * Returns the registration creation timestamp.
   *
   * @return int
   *   Creation timestamp of the registration.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the registration creation timestamp.
   *
   * @param int $timestamp
   *   The registration creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp): RegistrationInterface;

  /**
   * Sets associated event.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity that has an associated event.
   *
   * @return $this
   */
  public function setEvent(ContentEntityInterface $entity): RegistrationInterface;

  /**
   * Checks to see if this registration is confirmed.
   *
   * @return bool
   *   Whether or not this registration is confirmed.
   */
  public function isConfirmed(): bool;

  /**
   * Sets the registration to confirmed (or unconfirmed).
   *
   * @param bool $confirmed
   *   Whether to set confirmed or unconfirmed.
   *
   * @return $this
   */
  public function setConfirmed($confirmed): RegistrationInterface;

  /**
   * Gets the User object that owns this registration.
   *
   * @return \Drupal\user\UserInterface
   *   The User object.
   */
  public function getOwner(): UserInterface;

  /**
   * Sets the owner of the registration to object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account object.
   *
   * @return $this
   */
  public function setOwner(UserInterface $account): RegistrationInterface;

  /**
   * Gets the owner uid of this registration.
   *
   * @return int
   *   The uid for the owner.
   */
  public function getOwnerId(): int;

  /**
   * Set the owner of this registration by UID.
   *
   * @param int $uid
   *   The User ID.
   *
   * @return $this
   */
  public function setOwnerId($uid): RegistrationInterface;

  /**
   * Get a string representing the date of the event.
   *
   * @return string
   *   Date string to summarize the event date.
   */
  public function getDateString(): string;

  /**
   * Get registrants IDs for the registration.
   *
   * @return integer[]
   *   An array of registrant IDs.
   */
  public function getRegistrantIds(): array;

  /**
   * Get registrants for the registration.
   *
   * @return \Drupal\rng\Entity\RegistrantInterface[]
   *   An array of registrant entities.
   */
  public function getRegistrants(): array;

  /**
   * Get the number of registrants assigned to this registration.
   *
   * Returns the number of registrants with or without identities.
   *
   * @return int
   *   The value of the RegistrantQty field.
   */
  public function getRegistrantQty(): int;

  /**
   * Set the RegistrantQty field.
   *
   * This is the maximum number of registrants allowed to be attached to this
   * registration, or 0 if unlimited.
   *
   * @param int $qty
   *   Number of registrants for this registration, or 0 for unlimited.
   *
   * @return $this
   *
   * @throws \Drupal\rng\Exception\MaxRegistrantsExceededException
   */
  public function setRegistrantQty($qty): RegistrationInterface;

  /**
   * Check to determine whether all registrants have been set on a registration.
   *
   * @return bool
   *   Whether a registration can add new registrants.
   */
  public function canAddRegistrants(): bool;

  /**
   * Searches registrants on this registration for an identity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $identity
   *   The identity to search.
   *
   * @return bool
   *   Whether the identity is a registrant.
   */
  public function hasIdentity(EntityInterface $identity): bool;

  /**
   * Shortcut to add a registrant entity.
   *
   * Take care to ensure the identity is not already on the registration.
   *
   * @param \Drupal\Core\Entity\EntityInterface $identity
   *   The identity to add.
   *
   * @return $this
   */
  public function addIdentity(EntityInterface $identity): RegistrationInterface;

  /**
   * Get groups for the registration.
   *
   * @return \Drupal\rng\Entity\GroupInterface[]
   *   An array of registration_group entities.
   */
  public function getGroups(): array;

  /**
   * Add a group to the registration.
   *
   * @param \Drupal\rng\Entity\GroupInterface $group
   *   The group to add.
   *
   * @return $this
   */
  public function addGroup(GroupInterface $group): RegistrationInterface;

  /**
   * Remove a group from the registration.
   *
   * @param int $group_id
   *   The ID of a registration_group entity.
   *
   * @return $this
   */
  public function removeGroup($group_id): RegistrationInterface;

}
