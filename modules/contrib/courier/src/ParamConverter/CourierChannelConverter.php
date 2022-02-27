<?php

namespace Drupal\courier\ParamConverter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\courier\ChannelInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\ParamConverter\ParamConverterInterface;

/**
 * Provides upcasting for a courier channel entity type ID.
 */
class CourierChannelConverter implements ParamConverterInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CourierChannelConverter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if ($definition = $this->entityTypeManager->getDefinition($value, FALSE)) {
      if ($definition->entityClassImplements(ChannelInterface::class)) {
        return $definition;
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'courier_channel');
  }

}
