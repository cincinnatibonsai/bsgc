<?php

/**
 * @file
 * Provide views data for RNG.
 */

/**
 * Implements hook_views_data_alter().
 */
function rng_views_data_alter(&$data) {
  $entity_type_manager = \Drupal::entityTypeManager();
  $event_manager = \Drupal::service('rng.event_manager');

  $event_types = $event_manager->getEventTypes();
  foreach ($event_types as $entity_type => $event_type_bundles) {
    $definition = $entity_type_manager->getDefinition($entity_type);
    if ($base_table = $definition->getBaseTable()) {
      $data[$base_table]['rng_event_register'] = [
        'field' => [
          'title' => t('Register for event'),
          'help' => t('Provides a link to register for the event.'),
          'id' => 'rng_event_register',
        ],
      ];

      $id_field = $definition->getKey('id');
      foreach ($event_type_bundles as $event_bundle) {
        $field_name = $event_bundle->getEventStartDateField();
        $field_start = $field_name . '_value';
        $field_end = $field_name . '_end_value';
        $table = $base_table . '__' . $field_name;
        $data['rng_date_' . $base_table]['table'] = [
          'join' => [
            $base_table . '_field_data'  => [
              'join_id' => 'rng_subquery',
              'subquery_alias' => 'rng_date_' . $base_table,
              'subquery_table' => $table,
              'subquery_groupby' => 'entity_id',
              'subquery_fields' => [
                'entity_id' => 'entity_id',
              ],
              'subquery_expressions' => [
                $field_start => "min($field_start)",
                $field_end => "max($field_end)",
              ],
              'left_field' => $id_field,
              'field' => 'entity_id',
              'table' => $table,
              'table formula' => $table,
              'extra' => [],
            ],
          ],
          // 'provider' => 'views',
        ];
        $data['rng_date_' . $base_table]['pretty_event_date'] = [
          'title' => t('Pretty Event Dates'),
          'help' => t('Provides simple expression of event dates'),
          'group' => $base_table,
          'field' => [
            'title' => t('Pretty Event Dates'),
            'help' => t('Formats the event start - end date as string'),
            'entity_type' => $base_table,
            'id' => 'event_date_string_field',
            'real field' => $event_bundle->getEventStartDateField() . '_value',
          ],
          'filter' => [
            'id' => 'single_date_filter',
            'title' => t('Single Date Filter'),
            'help' => t('Filter based on earliest start date'),
            'entity_type' => $base_table,
            'field_name' => $event_bundle->getEventStartDateField(),
            'real field' => $event_bundle->getEventStartDateField() . '_value',
          ],
          'sort' => [
            'id' => 'standard',
            'title' => t('Single Date Sort'),
            'help' => t('Sort based on earliest start date'),
            'real field' => $event_bundle->getEventStartDateField() . '_value',
          ],
        ];
      }
    }
  }
  $data['registration']['registration_date_string_field'] = [
    'title' => t('Event formatted date'),
    'group' => t('Event'),
    'field' => [
      'title' => t('Event date, formatted'),
      'help' => t('Returns a string for the start date -> end date'),
      'id' => 'registration_date_string_field',
    ],
  ];
  return $data;
}

/**
 * Implements hook_views_data().
 */
function rng_views_data_not() {
  $data['views']['table']['group'] = t('Custom Global');
  $data['views']['table']['join'] = [
    // #global is a special flag which allows a table to appear all the time.
    '#global' => [],
  ];

  $data['views']['registration_date_string_field'] = [
    'title' => t('Registration date string field'),
    'help' => t("Show the date string for a registration's event dates"),
    'field' => [
      'id' => 'registration_date_string_field',
    ],
  ];
  return $data;
}
