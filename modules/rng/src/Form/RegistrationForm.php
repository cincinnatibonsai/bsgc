<?php

namespace Drupal\rng\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\rng\EventManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for registrations.
 */
class RegistrationForm extends ContentEntityForm {

  /**
   * The registration entity.
   *
   * @var \Drupal\rng\Entity\RegistrationInterface
   */
  protected $entity;

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Injects extra services used by this form.
   *
   * This is done this way to avoid overriding the parent constructor.
   *
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function setUp(EventManagerInterface $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $self = parent::create($container);
    $self->setup($container->get('rng.event_manager'));
    return $self;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\rng\Entity\RegistrationInterface $registration */
    $registration = $this->getEntity();
    $current_user = $this->currentUser();

    $event = $registration->getEvent();
    $event_meta = $this->eventManager->getMeta($event);

    $form = parent::form($form, $form_state);

    if (!$registration->isNew()) {
      $form['#title'] = $this->t('Edit Registration', [
        '%event_label' => $event->label(),
        '%event_id' => $event->id(),
        '%registration_id' => $registration->id(),
      ]);
    }

    // Registrants are initially either a collection of stub registrant
    // entities, or a single blank one. After any data changes, registrants
    // include all associated with the registration.
    $registrants = $registration->getRegistrants();

    $form['registrants_before'] = [
      '#type' => 'value',
      '#value' => $registrants,
    ];

    $form['people'] = [
      '#type' => 'details',
      '#title' => $this->t('People'),
      '#description' => $this->t('Select people to associate with this registration.'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $event_type = $event_meta->getEventType();
    $form['people']['registrants'] = [
      '#type' => 'registrants',
      '#event' => $event,
      '#default_value' => $registrants,
      '#allow_creation' => $event_meta->getCreatableIdentityTypes(),
      '#allow_reference' => $event_meta->getIdentityTypes(),
      '#registration' => $registration,
      '#form_modes' => $event_type->getIdentityTypeEntityFormModes(),
      '#tree' => TRUE,
    ];

    if (!$registration->isNew()) {
      $form['revision_information'] = [
        '#type' => 'details',
        '#title' => $this->t('Revisions'),
        '#optional' => TRUE,
        '#open' => $current_user->hasPermission('administer rng'),
        '#weight' => 20,
      ];
      $form['revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#description' => $this->t('Revisions record changes between saves.'),
        '#default_value' => FALSE,
        '#access' => $current_user->hasPermission('administer rng'),
        '#group' => 'revision_information',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\rng\Entity\RegistrationInterface $registration */
    $registration = parent::validateForm($form, $form_state);

    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants_before */
    $registrants_before = $form_state->getValue('registrants_before');
    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants_after */
    $registrants_after = $form_state->getValue(['people', 'registrants']);

    // Registrants.
    $registrants_after_ids = [];
    foreach ($registrants_after as $registrant) {
      if (!$registrant->isNew()) {
        $registrants_after_ids[] = $registrant->id();
      }
    }

    // Delete old registrants if they are not needed.
    $registrants_delete = [];
    foreach ($registrants_before as $i => $registrant) {
      if (!$registrant->isNew()) {
        if (!in_array($registrant->id(), $registrants_after_ids)) {
          $registrants_delete[] = $registrant;
        }
      }
    }

    $form_state->set('registrants_after', $registrants_after);
    $form_state->set('registrants_delete', $registrants_delete);

    return $registration;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $registration = $this->entity;

    $t_args = [
      '@type' => $registration->bundle(),
      '%label' => $registration->label(),
      '%id' => $registration->id(),
    ];

    if (!$registration->isNew()) {
      $registration->setNewRevision(!$form_state->isValueEmpty('revision'));
    }

    if ($registration->save() == SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Registration has been created.', $t_args));
    }
    else {
      $this->messenger()->addMessage($this->t('Registration was updated.', $t_args));
    }

    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants */
    $registrants = $form_state->get('registrants_after');
    foreach ($registrants as $registrant) {
      $registrant->setRegistration($registration);
      $registrant->save();
    }

    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants_delete */
    $registrants_delete = $form_state->get('registrants_delete');
    foreach ($registrants_delete as $registrant) {
      $registrant->delete();
    }

    $event = $registration->getEvent();
    if ($registration->access('view')) {
      $form_state->setRedirectUrl($registration->toUrl());
    }
    else {
      $form_state->setRedirectUrl($event->toUrl());
    }
  }

}
