<?php

namespace Drupal\rng\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rng\EventManagerInterface;
use Drupal\Core\Condition\ConditionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Base RNG selection plugin.
 */
class RNGSelectionBase extends DefaultSelection {

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * An identity entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|null
   */
  protected $entityType;

  /**
   * A meta event wrapper.
   *
   * Exists only when the selection is limited by event type.
   *
   * @var \Drupal\rng\EventMetaInterface|null
   */
  protected $eventMeta;

  /**
   * Constructs a new RNGSelectionBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, EventManagerInterface $event_manager, ConditionManager $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $entity_field_manager, $entity_type_bundle_info, $entity_repository);

    if (isset($this->configuration['handler_settings']['event_entity_type'], $this->configuration['handler_settings']['event_entity_id'])) {
      $event = $this->entityTypeManager->getStorage($this->configuration['handler_settings']['event_entity_type'])->load($this->configuration['handler_settings']['event_entity_id']);
      $this->eventMeta = $event_manager->getMeta($event);
    }
    else {
      throw new \Exception('RNG selection handler requires event context.');
    }

    $this->conditionManager = $condition_manager;
    $this->entityType = $this->entityTypeManager->getDefinition($this->configuration['target_type']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('rng.event_manager'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * Removes existing registered identities from the query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query to modify.
   */
  protected function removeDuplicateRegistrants(QueryInterface &$query) {
    if (!$this->eventMeta->duplicateRegistrantsAllowed()) {
      $entity_ids = [];

      $registrants = $this->eventMeta->getRegistrants($this->entityType->id());
      foreach ($registrants as $registrant) {
        $entity_ids[] = $registrant->getIdentityId()['entity_id'];
      }

      if ($entity_ids) {
        $query->condition($this->entityType->getKey('id'), $entity_ids, 'NOT IN');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    $query->addTag('rng_register');
    $this->removeDuplicateRegistrants($query);
    return $query;
  }

}
