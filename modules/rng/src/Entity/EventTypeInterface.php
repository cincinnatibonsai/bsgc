<?php

namespace Drupal\rng\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an event type config entity.
 */
interface EventTypeInterface extends ConfigEntityInterface {

  /**
   * Get event entity type ID.
   *
   * @return string|null
   *   An entity type ID.
   */
  public function getEventEntityTypeId(): ?string;

  /**
   * Sets the event entity type ID.
   *
   * @param string $entity_type
   *   An entity type ID.
   *
   * @return $this
   */
  public function setEventEntityTypeId(string $entity_type): EventTypeInterface;

  /**
   * Get event bundle.
   *
   * @return string|null
   *   A bundle name.
   */
  public function getEventBundle(): ?string;

  /**
   * Sets the event bundle.
   *
   * @param string $bundle
   *   A bundle name.
   *
   * @return $this
   */
  public function setEventBundle(string $bundle): EventTypeInterface;

  /**
   * Gets which permission on event entity grants 'event manage' permission.
   *
   * @return string|null
   *   The permission that allows someone to manage the event.
   */
  public function getEventManageOperation(): ?string;

  /**
   * Sets operation to mirror from the event entity.
   *
   * @param string $permission
   *   The operation to mirror.
   *
   * @return $this
   */
  public function setEventManageOperation(string $permission);

  /**
   * Gets whether anonymous registrants should be created/used.
   *
   * @return bool
   *   The setting.
   */
  public function getAllowAnonRegistrants(): bool;

  /**
   * Set whether or not to allow anonymous registrants.
   *
   * @param bool $allow_anon_registrants
   *   True if anonymous registrants are allowed.
   *
   * @return $this
   */
  public function setAllowAnonRegistrants(bool $allow_anon_registrants): EventTypeInterface;

  /**
   * Gets whether registrants should automatically sync with their identities.
   *
   * @return bool
   *   The setting.
   */
  public function getAutoSyncRegistrants(): bool;

  /**
   * Enables or disables automatically sync identity data with registrant data.
   *
   * @param bool $auto_sync_registrants
   *   True if registrants should be synced with identities, false otherwise.
   *
   * @return $this
   */
  public function setAutoSyncRegistrants(bool $auto_sync_registrants): EventTypeInterface;

  /**
   * Returns if existing users should be added as identities when email matches.
   *
   * @return bool
   *   The setting.
   */
  public function getAutoAttachUsers(): bool;

  /**
   * Sets if user identities that match by email should be automatically added.
   *
   * @param bool $auto_attach_users
   *   True if registrants should be attached to user identities by email.
   *
   * @return $this
   */
  public function setAutoAttachUsers(bool $auto_attach_users): EventTypeInterface;

  /**
   * Returns the registrant email field name to use for sync.
   *
   * @return string|null
   *   The setting.
   */
  public function getRegistrantEmailField(): ?string;

  /**
   * Set the machine name of an email field on the registrant to use for sync.
   *
   * @param string $registrant_email_field
   *   The name of the email field on the registrant entity type.
   *
   * @return $this
   */
  public function setRegistrantEmailField(string $registrant_email_field): EventTypeInterface;

  /**
   * Returns the start date field name used on the event entity.
   *
   * @return string|bool
   *   The start date field.
   */
  public function getEventStartDateField();

  /**
   * Set the machine name of the start date field.
   *
   * @param string $event_start_date_field
   *   The name of the start date field.
   *
   * @return $this
   */
  public function setEventStartDateField(string $event_start_date_field): EventTypeInterface;

  /**
   * Returns the end date field name used on the event entity.
   *
   * @return string|bool
   *   The end date field.
   */
  public function getEventEndDateField();

  /**
   * Set the machine name of the end date field. Uses end_value if daterange.
   *
   * @param string $event_end_date_field
   *   The name of the end date field.
   *
   * @return $this
   */
  public function setEventEndDateField(string $event_end_date_field): EventTypeInterface;

  /**
   * Whether to allow event managers to customize default rules.
   *
   * @return bool
   *   Whether event managers are allowed to customize default rules.
   */
  public function getAllowCustomRules(): bool;

  /**
   * Sets whether event managers can customize default rules.
   *
   * @param bool $allow
   *   Whether event managers are allowed to customize default rules.
   *
   * @return $this
   */
  public function setAllowCustomRules($allow): EventTypeInterface;

  /**
   * Registrant type for new registrants associated with this event type.
   *
   * @return string|null
   *   The Registrant type used for new registrants associated with this event
   *   type.
   */
  public function getDefaultRegistrantType();

  /**
   * Default messages configured for this event type.
   *
   * @return array
   *   The default messages array.
   */
  public function getDefaultMessages(): array;

  /**
   * Sets default messages for this event type.
   *
   * @param array $messages
   *   Default messages array.
   *
   * @return $this
   */
  public function setDefaultMessages(array $messages): EventTypeInterface;

  /**
   * Whether an identity type can be created.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   *
   * @return bool
   *   Whether an identity type can be created.
   */
  public function canIdentityTypeCreate(string $entity_type, string $bundle): bool;

  /**
   * Set whether an identity type can be created.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   * @param bool $enabled
   *   Whether the identity type can be created.
   *
   * @return $this
   */
  public function setIdentityTypeCreate(string $entity_type, string $bundle, bool $enabled): EventTypeInterface;

  /**
   * Get the form display mode used when the identity is created inline.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   *
   * @return string
   *   The form display mode used when the identity is created inline.
   */
  public function getIdentityTypeEntityFormMode(string $entity_type, string $bundle): string;

  /**
   * Get the form display modes for creating identities inline.
   *
   * @return array
   *   An array keyed as follows: [entity_type][bundle] = form_mode.
   */
  public function getIdentityTypeEntityFormModes(): array;

  /**
   * Set the form display mode used when the identity is created inline.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   * @param string $form_mode
   *   The form mode ID.
   *
   * @return $this
   */
  public function setIdentityTypeEntityFormMode(string $entity_type, string $bundle, string $form_mode): EventTypeInterface;

  /**
   * Whether an existing identity type can be referenced.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   *
   * @return bool
   *   Whether an existing identity type can be referenced.
   */
  public function canIdentityTypeReference(string $entity_type, string $bundle): bool;

  /**
   * Set whether existing identity type can be referenced.
   *
   * @param string $entity_type
   *   The identity entity type ID.
   * @param string $bundle
   *   The identity bundle.
   * @param bool $enabled
   *   Whether existing identity type can be referenced.
   *
   * @return $this
   */
  public function setIdentityTypeReference(string $entity_type, string $bundle, bool $enabled): EventTypeInterface;

  /**
   * Set registrant type for new registrants associated with this event type.
   *
   * @param string|null $registrant_type_id
   *   The Registrant type used for new registrants associated with this event
   *   type.
   *
   * @return $this
   */
  public function setDefaultRegistrantType($registrant_type_id): EventTypeInterface;

  /**
   * Create or clean up courier_context if none exist for an entity type.
   *
   * @param string $entity_type
   *   Entity type of the event type.
   * @param string $operation
   *   An operation: 'create' or 'delete'.
   */
  public static function courierContextCc(string $entity_type, string $operation);

}
