<?php

namespace Drupal\rng;

use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Allow a condition to modify an Entity Query.
 */
interface RNGConditionInterface {

  /**
   * Modify a query with condition configuration.
   *
   * This does not rely on any contexts, only valid configuration.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query object.
   */
  public function alterQuery(QueryInterface &$query);

}
