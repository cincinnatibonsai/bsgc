<?php

namespace Drupal\rng\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an event type rule config entity.
 */
interface EventTypeRuleInterface extends ConfigEntityInterface {

  /**
   * Returns the entity type for the event type rule.
   *
   * @return string
   *   The entity type used for the event type rule.
   */
  public function getEventEntityTypeId(): string;

  /**
   * Returns the bundle for the event type rule.
   *
   * @return string
   *   The entity bundle used for the event type rule.
   */
  public function getEventBundle(): string;

  /**
   * Returns the unique machine name for the event type rule.
   *
   * @return string
   *   The event type rule machine name.
   */
  public function getMachineName(): string;

  /**
   * Returns the trigger for the event type rule.
   *
   * @return string
   *   The event type rule trigger.
   */
  public function getTrigger(): string;

  /**
   * Returns all condition plugin configurations.
   *
   * @return array
   *   Configuration for all configured condition plugins.
   */
  public function getConditions(): array;

  /**
   * Returns all action plugin configurations.
   *
   * @return array
   *   Configuration for all configured action plugins.
   */
  public function getActions(): array;

  /**
   * Returns a condition configuration.
   *
   * @param string $name
   *   A condition plugin instance ID.
   *
   * @return array|null
   *   The condition configuration or null, in case the requested condition is
   *   not defined.
   */
  public function getCondition(string $name): ?array;

  /**
   * Returns a action configuration.
   *
   * @param string $name
   *   A action plugin instance ID.
   *
   * @return array|null
   *   The action configuration or null, in case the requested action is not
   *   defined.
   */
  public function getAction(string $name): ?array;

  /**
   * Sets a condition configuration.
   *
   * @param string $name
   *   A condition plugin instance ID.
   * @param array $configuration
   *   The condition plugin configuration.
   *
   * @return $this
   */
  public function setCondition(string $name, array $configuration): EventTypeRuleInterface;

  /**
   * Sets an action configuration.
   *
   * @param string $name
   *   A action plugin instance ID.
   * @param array $configuration
   *   The action plugin configuration.
   *
   * @return $this
   */
  public function setAction(string $name, array $configuration): EventTypeRuleInterface;

  /**
   * Removes a condition configuration.
   *
   * @param string $name
   *   A condition plugin instance ID.
   *
   * @return $this
   */
  public function removeCondition(string $name): EventTypeRuleInterface;

  /**
   * Removes an action configuration.
   *
   * @param string $name
   *   A action plugin instance ID.
   *
   * @return $this
   */
  public function removeAction(string $name): EventTypeRuleInterface;

}
