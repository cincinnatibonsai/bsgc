<?php

namespace Drupal\courier\Plugin\IdentityChannel;

use Drupal\courier\ChannelInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for IdentityChannel plugins.
 */
interface IdentityChannelPluginInterface {

  /**
   * Inserts the identity into the message.
   *
   * @param \Drupal\courier\ChannelInterface $message
   *   The message. Passed by reference.
   * @param \Drupal\Core\Entity\EntityInterface $identity
   *   The identity.
   *
   * @throws \Drupal\courier\Exception\IdentityException
   *   Thrown when an identity cannot be applied. Message is discarded, it does
   *   not stop creation of remaining messages in collection.
   */
  public function applyIdentity(ChannelInterface &$message, EntityInterface $identity);

}
