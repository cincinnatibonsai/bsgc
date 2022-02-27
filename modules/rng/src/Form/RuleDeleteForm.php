<?php

namespace Drupal\rng\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for deleting a rng rule.
 */
class RuleDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete this rule?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->getEvent()->toUrl();
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
    $rule = $this->entity;
    $rule->delete();
    $event = $rule->getEvent();

    $this->messenger()->addMessage(t('Rule deleted.'));

    if ($urlInfo = $event->toUrl()) {
      $form_state->setRedirectUrl($urlInfo);
    }
  }

}
