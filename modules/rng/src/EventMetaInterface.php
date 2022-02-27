<?php

namespace Drupal\rng;

use Drupal\rng\Entity\RegistrationTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for EventMeta.
 */
interface EventMetaInterface {

  /**
   * Value indicating unlimited registration capacity for an event.
   */
  const CAPACITY_UNLIMITED = -1;

  /**
   * Instantiates a new instance of EventMeta handler.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The event entity.
   *
   * @return static
   *   A new EventMeta instance.
   */
  public static function createInstance(ContainerInterface $container, EntityInterface $entity);

  /**
   * Get the event entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The event entity.
   */
  public function getEvent();

  /**
   * Returns the event type for the event.
   *
   * @return \Drupal\rng\Entity\EventTypeInterface
   *   The event type for the event.
   */
  public function getEventType();

  /**
   * Checks if this event is accepting new registrations.
   *
   * This method only checks configuration. Instead you may want to check
   * 'create' operation using entity_access.
   *
   * @return bool
   *   Whether this event is accepting new registrations.
   */
  public function isAcceptingRegistrations();

  /**
   * Returns the reply-to e-mail address for mails sent from this event.
   *
   * @return string
   *   A mail address.
   */
  public function getReplyTo();

  /**
   * Checks if a registrant is allowed to register more than once on this event.
   *
   * @return bool
   *   Whether duplicate registrants are allowed.
   */
  public function duplicateRegistrantsAllowed();

  /**
   * Gets a list of registration types IDs allowed for this event.
   *
   * @return string[]
   *   An array of registration_type IDs.
   */
  public function getRegistrationTypeIds();

  /**
   * Gets a list of registration types allowed for this event.
   *
   * @return \Drupal\rng\Entity\RegistrationTypeInterface[]
   *   An array of registration_type entities.
   */
  public function getRegistrationTypes();

  /**
   * Checks if a registration type is allowed to be used on an event.
   *
   * @param \Drupal\rng\Entity\RegistrationTypeInterface $registration_type
   *   A registration type entity.
   *
   * @return bool
   *   Whether the registration type can be used.
   */
  public function registrationTypeIsValid(RegistrationTypeInterface $registration_type);

  /**
   * Removes references to an allowed registration type from the event.
   *
   * @param string $registration_type_id
   *   The ID of a registration_type entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The modified event.
   */
  public function removeRegistrationType($registration_type_id);

  /**
   * Removes references to a default group from the event.
   *
   * @param int $group_id
   *   The ID of a registration_group entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The modified event.
   */
  public function removeGroup($group_id);

  /**
   * Gets configuration for maximum permitted registrants on this event.
   *
   * @return int|EventMetaInterfaceCAPACITY_UNLIMITED
   *   Maximum amount of registrants (>= 0), or unlimited.
   */
  public function getRegistrantCapacity();

  /**
   * Calculates how many more registrants can be added to this event.
   *
   * This value will not be negative if there are excessive registrations.
   *
   * @return int|EventMetaInterfaceCAPACITY_UNLIMITED
   *   Number of new registrants allowed (>= 0), or unlimited.
   */
  public function remainingRegistrantCapacity();

  /**
   * Returns if there still is capacity left on the event.
   *
   * @return bool
   *   True, if there is still capacity left on the event. False otherwise.
   */
  public function hasCapacity(): bool;

  /**
   * Checks if a registrant is allowed to register on a wait list on this event.
   *
   * @return bool
   *   Whether wait list registrations are allowed.
   */
  public function allowWaitList();

  /**
   * Get groups that should be added to all new registrations.
   *
   * @return \Drupal\rng\Entity\GroupInterface[]
   *   An array of group entities.
   */
  public function getDefaultGroups();

  /**
   * Builds a entity query with conditions referencing this event.
   *
   * Assumes there is a dynamic_entity_reference field on the entity_type named
   * 'event'.
   *
   * @param string $entity_type
   *   An entity type with an 'event' DER field attached.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildQuery($entity_type);

  /**
   * Builds a entity query for registrations with conditions referencing this
   * event.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildRegistrationQuery();

  /**
   * Builds an entity query for registrants with conditions referencing this
   * event via the registration entity.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildEventRegistrantQuery();

  /**
   * Get all registrations for this event.
   *
   * @return \Drupal\rng\Entity\RegistrationInterface[]
   *   An array of registration entities.
   */
  public function getRegistrations();

  /**
   * Count how many registrations are on this event.
   *
   * @return int
   *   Number of registrations on this event.
   */
  public function countRegistrations();

  /**
   * Builds a entity query for rules with conditions referencing this event.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildRuleQuery();

  /**
   * Get all rules for this event.
   *
   * @param string|null $trigger
   *   The trigger ID for the rule.
   * @param bool $defaults
   *   If there are no rules in the database, generate some unsaved rules.
   * @param bool $is_active
   *   The status of the rules, or set to NULL for any status.
   *
   * @return \Drupal\rng\Entity\RuleInterface[]
   *   An array of rng_rule entities keyed by rule ID.
   */
  public function getRules($trigger = NULL, $defaults = FALSE, $is_active = TRUE);

  /**
   * Returns site default access rules and associated conditions and actions.
   *
   * @param string $trigger
   *   The trigger ID for the rules.
   *
   * @return \Drupal\rng\Entity\RuleInterface[]
   *   An array of rng_rule entities.
   */
  public function getDefaultRules($trigger = NULL);

  /**
   * Determines if this event should use site default rules.
   *
   * If the event has no rules defined, this will determine if site default
   * rules should be used.
   *
   * @param string $trigger
   *   The trigger ID for the rules.
   *
   * @return bool
   *   Whether site default rules should be used.
   */
  public function isDefaultRules($trigger);

  /**
   * Manually triggers rules for this event.
   *
   * @param string $trigger
   *   The trigger ID.
   * @param array $context
   *   Mixed context.
   */
  public function trigger($trigger, array $context = []);

  /**
   * Builds an entity query for groups with conditions referencing this event.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildGroupQuery();

  /**
   * Returns all groups for this event.
   *
   * @return \Drupal\rng\Entity\GroupInterface[]
   *   An array of registration_group entities.
   */
  public function getGroups();

  /**
   * Builds an entity query for registrants associated to registrations
   * referencing this event.
   *
   * @param string $entity_type_id
   *   The registrant entity type, or NULL to get all.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  public function buildRegistrantQuery($entity_type_id = NULL);

  /**
   * Returns all registrants for this event.
   *
   * @param string $entity_type_id
   *   The registrant entity type, or NULL to get all.
   *
   * @return \Drupal\rng\Entity\RegistrantInterface[]
   *   An array of registrant entities.
   */
  public function getRegistrants($entity_type_id = NULL);

  /**
   * Determines if the current user has proxy register access.
   *
   * Includes whether the current user can create an identity.
   *
   * @return bool
   *   Whether the current user can create an identity or reference at least one
   *   identity.
   */
  public function canRegisterProxyIdentities();

  /**
   * Counts number of identities the current user has proxy register access.
   *
   * This number includes the current user. It also only considers existing
   * identities, it does not include the ability to 'create' new identities.
   *
   * @return int
   *   Number of identities.
   */
  public function countProxyIdentities();

  /**
   * Returns identity types which can be referenced for this event.
   *
   * The types returned are guaranteed to exist in the system. Invalid
   * configuration such as no-longer existing bundles or entity types are
   * filtered out.
   *
   * @return array
   *   Array of bundles keyed by entity type.
   */
  public function getIdentityTypes();

  /**
   * Returns identity types which can be created for this event.
   *
   * The types returned are guaranteed to exist in the system. Invalid
   * configuration such as no-longer existing bundles or entity types are
   * filtered out.
   *
   * @return array
   *   Array of bundles keyed by entity type.
   */
  public function getCreatableIdentityTypes();

  /**
   * Determines if identities can register.
   *
   * @param string $entity_type
   *   An identity entity type ID.
   * @param int[] $entity_ids
   *   An array of identity entity IDs.
   *
   * @return integer[]
   *   An array of ID's of the identities that can register.
   */
  public function identitiesCanRegister($entity_type, array $entity_ids);

  /**
   * Clones the site default access rules onto the event.
   *
   * If the site default rules change in the future, the access rules for this
   * event will not get automatically updated.
   *
   * Access rules determine registration operation grants.
   */
  public function addDefaultAccess();

  /**
   * Creates messages for Event from Default messages for this Event Type.
   */
  public function createDefaultEventMessages();

  /**
   * Checks whether the event date is in the past.
   *
   * @param bool $use_end_date
   *   Set to TRUE to check whether end date is in the past. FALSE checks start
   *   date.
   *
   * @return bool
   *   Whether the event start or end date is in the past.
   */
  public function isPastEvent($use_end_date = FALSE);

  /**
   * Formats the dates of this event.
   *
   * @return string
   *   A string expressing the date range for the event.
   */
  public function getDateString();

}
