<?php

namespace Drupal\rng\Routing\Enhancer;

use Drupal\Core\Routing\EnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route enhancer for RNG.
 */
class RngRouteEnhancer implements EnhancerInterface {

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    if (!empty($defaults['event'])) {
      $event_entity_type = $defaults['event'];
      if (isset($defaults[$event_entity_type])) {
        $rng_event = $defaults[$event_entity_type];
        $defaults['rng_event'] = $rng_event;
      }
    }
    return $defaults;
  }

}
