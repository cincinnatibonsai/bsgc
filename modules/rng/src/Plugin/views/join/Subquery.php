<?php

namespace Drupal\rng\Plugin\views\join;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Join handler for relationships that join with a subquery as a table.
 *
 * For example:
 *
 * @code
 * LEFT JOIN (SELECT subquery_fields[], subquery_expressions[]
 *   WHERE subquery_where GROUP BY subquery_groupby) table
 * ON base_table.left_field = table.field
 * @endcode
 *
 * Join definition: same as \Drupal\views\Plugin\views\join\JoinPluginBase,
 * plus:
 * - subquery_fields[]
 * - subquery_expressions[]
 * - subquery_where
 * - subquery_groupbysubquery_
 *
 * See https://www.drupal.org/project/drupal/issues/3125146.
 *
 * @ingroup views_join_handlers
 * @ViewsJoin("rng_subquery")
 */
class Subquery extends JoinPluginBase implements ContainerFactoryPluginInterface {

  /**
   * A database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * Builds the SQL for the join this object represents.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $select_query
   *   The select query object.
   * @param string $table
   *   The base table to join.
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $view_query
   *   The source views query.
   */
  public function buildJoin(SelectInterface $select_query, string $table, QueryPluginBase $view_query) {
    $alias = $this->configuration['subquery_alias'];
    $subquery = $this->database->select($this->configuration['subquery_table'], $alias);

    if (!empty($this->configuration['subquery_fields'])) {
      foreach ($this->configuration['subquery_fields'] as $field_alias => $field) {
        $subquery->addField($alias, $field, $field_alias);
      }
    }
    if (!empty($this->configuration['subquery_expressions'])) {
      foreach ($this->configuration['subquery_expressions'] as $field_alias => $expression) {
        $subquery->addExpression($expression, $field_alias);
      }
    }
    if (!empty($this->configuration['subquery_groupby'])) {
      $subquery->groupBy($this->configuration['subquery_groupby']);
    }
    if (!empty($this->configuration['subquery_where'])) {
      foreach ($this->configuration['subquery_where'] as $condition) {
        $subquery->where($condition);
      }
    }

    $right_table = $subquery;

    $left_table = $view_query->getTableInfo($this->leftTable);
    $left_field = "$left_table[alias].$this->leftField";

    // Add our join condition, using a subquery on the left instead of a field.
    $condition = "$left_field = $table[alias].$this->field";
    $arguments = [];

    // Tack on the extra.
    // This is just copied verbatim from the parent class, which itself has a
    // bug: https://www.drupal.org/node/1118100.
    if (isset($this->extra)) {
      if (is_array($this->extra)) {
        $extras = [];
        foreach ($this->extra as $info) {
          // Figure out the table name. Remember, only use aliases provided
          // if at all possible.
          $join_table = '';
          if (!array_key_exists('table', $info)) {
            $join_table = $table['alias'] . '.';
          }
          elseif (isset($info['table'])) {
            $join_table = $info['table'] . '.';
          }

          $placeholder = ':views_join_condition_' . $select_query->nextPlaceholder();

          if (is_array($info['value'])) {
            $operator = !empty($info['operator']) ? $info['operator'] : 'IN';
            // Transform from IN() notation to = notation if just one value.
            if (count($info['value']) == 1) {
              $info['value'] = array_shift($info['value']);
              $operator = $operator == 'NOT IN' ? '!=' : '=';
            }
          }
          else {
            $operator = !empty($info['operator']) ? $info['operator'] : '=';
          }

          $extras[] = "$join_table$info[field] $operator $placeholder";
          $arguments[$placeholder] = $info['value'];
        }

        if ($extras) {
          if (count($extras) == 1) {
            $condition .= ' AND ' . array_shift($extras);
          }
          else {
            $condition .= ' AND (' . implode(' ' . $this->extraOperator . ' ', $extras) . ')';
          }
        }
      }
      elseif ($this->extra && is_string($this->extra)) {
        $condition .= " AND ($this->extra)";
      }
    }
    $select_query->addJoin($this->type, $right_table, $table['alias'], $condition, $arguments);
  }

}
