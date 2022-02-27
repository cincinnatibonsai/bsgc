<?php

namespace Drupal\rng\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the event type entity.
 *
 * @ConfigEntityType(
 *   id = "rng_event_type_rule",
 *   label = @Translation("Event type rule"),
 *   admin_permission = "administer event types",
 *   config_prefix = "rule",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id"
 *   },
 *   config_export = {
 *     "id",
 *     "machine_name",
 *     "entity_type",
 *     "bundle",
 *     "trigger",
 *     "conditions",
 *     "actions",
 *   }
 * )
 */
class EventTypeRule extends ConfigEntityBase implements EventTypeRuleInterface {

  /**
   * The event entity type.
   *
   * @var string
   */
  protected $entity_type;

  /**
   * The event bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * A unique machine name for this rule.
   *
   * @var string
   */
  protected $machine_name;

  /**
   * The trigger for the rule.
   *
   * @var string
   */
  protected $trigger;

  /**
   * Condition plugin configurations.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Actions plugin configurations.
   *
   * @var array
   */
  protected $actions = [];

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->entity_type . '.' . $this->bundle . '.' . $this->machine_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventEntityTypeId(): string {
    return $this->entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventBundle(): string {
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineName(): string {
    return $this->machine_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getTrigger(): string {
    return $this->trigger;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions(): array {
    return $this->conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function getActions(): array {
    return $this->actions;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($name): ?array {
    return $this->conditions[$name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAction($name): ?array {
    return $this->actions[$name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setCondition($name, $configuration): EventTypeRuleInterface {
    $this->conditions[$name] = $configuration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAction($name, $configuration): EventTypeRuleInterface {
    $this->actions[$name] = $configuration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($name): EventTypeRuleInterface {
    unset($this->conditions[$name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAction($name): EventTypeRuleInterface {
    unset($this->actions[$name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    if ($event_type = RngEventType::load($this->getEventEntityTypeId() . '.' . $this->getEventBundle())) {
      $this->addDependency('config', $event_type->getConfigDependencyName());
    }

    return $this;
  }

}
