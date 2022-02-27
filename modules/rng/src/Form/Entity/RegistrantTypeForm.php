<?php

namespace Drupal\rng\Form\Entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rng\Entity\RegistrantTypeInterface;
use Drupal\rng\Entity\RegistrantType;

/**
 * Form controller for Registrant types.
 */
class RegistrantTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $registrant_type = $this->entity;

    if (!$registrant_type->isNew()) {
      $form['#title'] = $this->t('Edit registrant type %label', [
        '%label' => $registrant_type->label(),
      ]);
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $registrant_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $registrant_type->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#default_value' => $registrant_type->description,
    ];

    $form['label_pattern'] = [
      '#type' => 'textfield',
      '#title' => t('Label Pattern'),
      '#default_value' => $registrant_type->label_pattern,
      '#description' => t('For anonymous registrants, provide a pattern here to use as its label. Only used if the registrant does not have another identify in the system. Can use tokens - e.g. <b>[registrant:field_first_name] [registrant:field_last_name]</b>'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($entity_id) {
    return RegistrantType::load($entity_id) instanceof RegistrantTypeInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $registrant_type = $this->getEntity();
    $status = $registrant_type->save();

    $t_args = ['%label' => $registrant_type->label()];

    if ($status === SAVED_NEW) {
      $this->logger('rng')->notice('%label registrant type was added.', $t_args);
      $this->messenger()->addMessage($this->t('%label registrant type was added.', $t_args));
    }
    else {
      $this->logger('rng')->notice('%label registrant type has been updated.', $t_args);
      $this->messenger()->addMessage($this->t('%label registrant type has been updated.', $t_args));
    }

    $form_state->setRedirect('entity.registrant_type.collection');
  }

}
