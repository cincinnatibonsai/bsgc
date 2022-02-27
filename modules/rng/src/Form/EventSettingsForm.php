<?php

namespace Drupal\rng\Form;

use Drupal\Core\Form\FormBase;
use Drupal\rng\EventManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure event settings.
 */
class EventSettingsForm extends FormBase {

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Constructs a new MessageActionForm object.
   *
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function __construct(EventManagerInterface $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('rng.event_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rng_event_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $rng_event = NULL) {
    $entity = clone $rng_event;
    $form_state->set('event', $entity);

    $display = \Drupal::service('entity_display.repository')->getFormDisplay($entity->getEntityTypeId(), $entity->bundle(), 'rng_event');
    $form_state->set('form_display', $display);

    $form['event'] = ['#weight' => 0];
    $display->buildForm($entity, $form['event'], $form_state);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityInterface $event */
    $event = $form_state->get('event');
    $form_state->get('form_display')->extractFormValues($event, $form, $form_state);
    $event->save();

    $t_args = ['%event_label' => $event->label()];
    $this->messenger()->addMessage(t('Event settings updated.', $t_args));
  }

}
