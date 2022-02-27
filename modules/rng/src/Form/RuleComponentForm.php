<?php

namespace Drupal\rng\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Form controller for rng rule components.
 */
class RuleComponentForm extends ContentEntityForm {

  /**
   * The action entity.
   *
   * @var \Drupal\rng\Entity\RuleComponentInterface
   */
  protected $entity;

  /**
   * The plugin entity.
   *
   * @var \Drupal\Core\Plugin\ContextAwarePluginBase
   *
   * @todo change when condition and action have a better common class.
   */
  protected $plugin;

  /**
   * The action manager service.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * The condition manager service.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->actionManager = $container->get('plugin.manager.action');
    $instance->conditionManager = $container->get('plugin.manager.condition');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->entity->getConfiguration();
    $manager = $this->entity->getType() == 'condition' ? 'conditionManager' : 'actionManager';
    $this->plugin = $this->{$manager}->createInstance($this->entity->getPluginId(), $config);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $action = $this->entity;

    if (!$action->isNew()) {
      $form['#title'] = $this->t('Edit @type',
        [
          '@type' => $action->getType(),
        ]
      );
    }
    $form = $this->plugin->buildConfigurationForm($form, $form_state);
    return parent::form($form, $form_state, $action);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $event = $this->entity->getRule()->getEvent();
    // Reset tags for event. Forces re-render of things like tabs.
    Cache::invalidateTags($event->getCacheTagsToInvalidate());

    $this->plugin->submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\rng\Entity\RuleComponentInterface $component */
    $component = $this->getEntity();
    $is_new = $component->isNew();
    $plugin_configuration = $this->plugin->getConfiguration();

    $component->setConfiguration($plugin_configuration);
    $component->save();

    $type = $this->entity->getType();
    $types = [
      'action' => $this->t('Action'),
      'condition' => $this->t('Condition'),
    ];
    $t_args = ['@type' => $types[$type] ?? $this->t('Component')];

    if ($is_new) {
      $this->messenger()->addMessage(t('@type created.', $t_args));
    }
    else {
      $this->messenger()->addMessage(t('@type updated.', $t_args));
    }
  }

}
