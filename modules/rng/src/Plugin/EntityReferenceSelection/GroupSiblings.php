<?php

namespace Drupal\rng\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\rng\Entity\GroupInterface;

/**
 * Provides selection to sibling groups on an event.
 *
 * Should be attached to a `registration_group` entity.
 *
 * @EntityReferenceSelection(
 *   id = "rng:registration_group:siblings",
 *   label = @Translation("Registration group siblings"),
 *   entity_types = {"registration_group"},
 *   group = "siblings",
 *   weight = 1
 * )
 */
class GroupSiblings extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    if (($registration_group = $this->configuration['entity']) instanceof GroupInterface) {
      /** @var \Drupal\rng\Entity\GroupInterface $registration_group */
      if (($event = $registration_group->getEvent()) instanceof EntityInterface) {
        $group = $query->andConditionGroup()
          ->condition('event__target_type', $event->getEntityTypeId(), '=')
          ->condition('event__target_id', $event->id(), '=');
        $query->condition($group);
      }
      $id_key = $registration_group->getEntityType()->getKey('id');
      $query->condition($id_key, [$registration_group->id()], 'NOT IN');
    }
    $query->condition('source', NULL, 'IS NULL');
    return $query;
  }

}
