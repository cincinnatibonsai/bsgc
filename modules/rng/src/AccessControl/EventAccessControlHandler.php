<?php

namespace Drupal\rng\AccessControl;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rng\Entity\RuleInterface;

/**
 * Access controller for the rules and rule components.
 */
class EventAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $account = $this->prepareUser($account);
    $child = $entity instanceof RuleInterface ? $entity : $entity->getRule();
    if ($child instanceof EntityInterface) {
      /** @var \Drupal\rng\Entity\RuleInterface|\Drupal\rng\Entity\RuleComponentInterface $child */
      return $child
        ->getEvent()
        ->access('manage event', $account, TRUE);
    }
    return AccessResult::neutral();
  }

}
