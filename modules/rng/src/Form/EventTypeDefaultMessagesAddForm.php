<?php

namespace Drupal\rng\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rng\Entity\EventTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a new default message to this event type.
 */
class EventTypeDefaultMessagesAddForm extends FormBase {

  /**
   * The event type object.
   *
   * @var \Drupal\rng\Entity\EventTypeInterface
   */
  public $eventType;

  /**
   * {@inheritdoc}
   */
  public function __construct(EventTypeInterface $event_type) {
    $this->eventType = $event_type;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')->getParameter('rng_event_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rng_event_default_message_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo Move this and other occurences into a common place?.
    $triggers = [
      'rng:custom:date' => $this->t('To all registrations, on a date.'),
      (string) $this->t('Registrations') => [
        'entity:registration:new' => $this->t('To a single registration, when it is created.'),
        'entity:registration:update' => $this->t('To a single registration, when it is updated.'),
      ],
    ];

    $form['trigger'] = [
      '#type' => 'select',
      '#options' => $triggers,
      '#title' => $this->t('Trigger'),
    ];
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];
    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#required' => TRUE,
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messages = $this->eventType->getDefaultMessages();
    array_push($messages, [
      'trigger' => $form_state->getValue('trigger'),
      'status' => $form_state->getValue('status'),
      'subject' => $form_state->getValue('subject'),
      'body' => $form_state->getValue('body'),
    ]);
    $this->eventType->setDefaultMessages($messages)->save();

    $this->messenger()->addMessage($this->t('New message added.'));
    $form_state->setRedirect('entity.rng_event_type.default_messages', ['rng_event_type' => $this->eventType->id()]);
  }

}
