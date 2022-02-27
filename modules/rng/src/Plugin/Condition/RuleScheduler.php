<?php

namespace Drupal\rng\Plugin\Condition;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rng\Entity\RuleInterface;
use Drupal\rng\Entity\RuleSchedule;
use Drupal\rng\Entity\RuleComponent;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Schedules rule execution based on the configured date.
 *
 * @Condition(
 *   id = "rng_rule_scheduler",
 *   label = @Translation("Rule scheduler")
 * )
 */
class RuleScheduler extends CurrentTime implements ContainerFactoryPluginInterface {

  use MessengerTrait;

  /**
   * The storage for rng_rule_scheduler entities.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $schedulerStorage;

  /**
   * Constructs a new RuleScheduler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->schedulerStorage = $entity_type_manager->getStorage('rng_rule_scheduler');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   - rng_rule_component: integer: Associated rule component ID.
   *   - rng_rule_scheduler: integer: ID of a rng_rule_schedule entity.
   *     If the ID is of a non-existent rng_rule_schedule entity, assume the
   *     rule has been executed successfully.
   *     The parent date configuration value is mirrored to the
   *     rng_rule_schedule entity when this form is saved.
   *   - negate: has no effect.
   */
  public function defaultConfiguration() {
    return [
      'rng_rule_component' => NULL,
      'rng_rule_scheduler' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * Get rule_component entity ID.
   */
  public function getRuleComponentId() {
    if (isset($this->configuration['rng_rule_component'])) {
      return $this->configuration['rng_rule_component'];
    }
    return NULL;
  }

  /**
   * Returns the rule scheduler entity, if defined.
   *
   * @return \Drupal\rng\Entity\RuleScheduleInterface|null
   *   A rule scheduler entity if one is configured. Null otherwise.
   */
  public function getRuleScheduler() {
    if (isset($this->configuration['rng_rule_scheduler'])) {
      return RuleSchedule::load($this->configuration['rng_rule_scheduler']);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['date']['#description'] = t('Rule will trigger once on this date.');

    $rule_scheduler = $this->getRuleScheduler();
    if ($rule_scheduler) {
      if ($rule_scheduler->getInQueue()) {
        $this->messenger()->addMessage($this->t('This message is queued for execution.'));
        $form['date']['#disabled'] = TRUE;
      }
    }

    unset($form['negate']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['negate'] = FALSE;
    $this->updateRuleSchedulerEntity();
  }

  /**
   * Create, update, or delete the associated rule scheduler entity.
   *
   * Depending on if it needs to exist.
   */
  public function updateRuleSchedulerEntity() {
    $rule_scheduler = $this->getRuleScheduler();
    $rule_component_id = $this->getRuleComponentId();
    $rule_component = $rule_component_id ? RuleComponent::load($rule_component_id) : NULL;
    $rule = $rule_component ? $rule_component->getRule() : NULL;
    $rule_active = $rule instanceof RuleInterface ? $rule->isActive() : FALSE;

    if ($rule_active) {
      if (!$rule_scheduler) {
        // Create the scheduler entity if it doesn't exist.
        $rule_scheduler = RuleSchedule::create([
          'component' => $this->getRuleComponentId(),
        ]);
        $rule_scheduler->save();
        $this->configuration['rng_rule_scheduler'] = $rule_scheduler->id();
      }

      // Mirror the date into the scheduler.
      if ($rule_scheduler) {
        $rule_scheduler->setDate($this->configuration['date']);
        $rule_scheduler->save();
      }
    }
    else {
      // Delete the rule scheduler if it is not in the queue already.
      if ($rule_scheduler && !$rule_scheduler->getInQueue()) {
        $rule_scheduler->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $rule_scheduler = $this->getRuleScheduler();
    if ($rule_scheduler) {
      return $this->t('Current date is after @date', [
        '@date' => DrupalDateTime::createFromTimestamp($rule_scheduler->getDate()),
      ]);
    }
    else {
      return $this->t('Current date is after a date');
    }
  }

}
