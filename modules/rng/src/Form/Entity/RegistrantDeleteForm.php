<?php

namespace Drupal\rng\Form\Entity;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for deleting a registrant.
 */
class RegistrantDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this registrant?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\rng\Entity\RegistrantInterface $registrant */
    $registrant = $this->entity;
    $registrant->delete();

    $this->messenger()->addMessage($this->t('Registrant deleted.'));

    $registration = $registrant->getRegistration();
    if ($url = $registration->toUrl()) {
      // Redirect to registration.
      $form_state->setRedirectUrl($url);
    }
    else {
      $form_state->setRedirect('<front>');
    }
  }

}
