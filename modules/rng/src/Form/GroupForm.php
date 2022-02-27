<?php

namespace Drupal\rng\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rng\Entity\GroupInterface;

/**
 * Form controller for registration groups.
 */
class GroupForm extends ContentEntityForm {

  /**
   * The registration group entity.
   *
   * @var \Drupal\rng\Entity\GroupInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state, GroupInterface $group = NULL) {
    $group = $this->entity;

    if (!$group->isNew()) {
      $form['#title'] = $this->t('Edit group %label',
        [
          '%label' => $group->label(),
        ]
      );
    }

    $form = parent::form($form, $form_state, $group);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $group = $this->entity
      ->setSource(NULL);
    $event = $group->getEvent();
    $is_new = $group->isNew();
    $group->save();

    $t_args = ['%label' => $group->label()];
    if ($is_new) {
      $this->messenger()->addMessage(t('Group %label has been created.', $t_args));
    }
    else {
      $this->messenger()->addMessage(t('Group %label was updated.', $t_args));
    }

    $form_state->setRedirect(
      'rng.event.' . $event->getEntityTypeId() . '.group.list',
      [$event->getEntityTypeId() => $event->id()]
    );
  }

}
