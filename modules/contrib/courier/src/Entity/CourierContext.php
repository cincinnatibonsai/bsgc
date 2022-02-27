<?php

namespace Drupal\courier\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\courier\CourierContextInterface;

/**
 * Defines a courier_context configuration entity.
 *
 * @ConfigEntityType(
 *   id = "courier_context",
 *   label = @Translation("Courier context"),
 *   config_prefix = "context",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "tokens",
 *   }
 * )
 */
class CourierContext extends ConfigEntityBase implements CourierContextInterface {

  /**
   * The context ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The context label.
   *
   * @var string
   */
  protected $label;

  /**
   * An array of token names.
   *
   * @var array
   */
  protected $tokens;

  /**
   * {@inheritdoc}
   */
  public function getTokens() {
    return array_merge($this->tokens, ['identity']);
  }

}
