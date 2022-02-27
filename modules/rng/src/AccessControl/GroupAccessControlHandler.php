<?php

namespace Drupal\rng\AccessControl;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\rng\Entity\GroupInterface;

/**
 * Access controller for groups.
 */
class GroupAccessControlHandler extends EntityAccessControlHandler {

  /**
   * Performs access checks.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A group entity.
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'view label', 'update' or
   *   'delete'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @throws \InvalidArgumentException
   *   In case the passed entity does not implement
   *   \Drupal\rng\Entity\GroupInterface.
   */
  protected function checkAccess(EntityInterface $entity, string $operation, AccountInterface $account) {
    if (!$entity instanceof GroupInterface) {
      throw new \InvalidArgumentException(strtr('The passed entity should implement @interface.', [
        '@interface' => GroupInterface::class,
      ]));
    }
    $account = $this->prepareUser($account);
    $event = $entity->getEvent();

    if (!$entity->isUserGenerated() && $operation == 'delete') {
      return AccessResult::forbidden();
    }

    if ($event) {
      return $event->access('manage event', $account, TRUE);
    }

    return AccessResult::neutral();
  }

}
