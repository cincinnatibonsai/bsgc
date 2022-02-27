<?php

namespace Drupal\rng\Exception;

/**
 * Thrown when an action would lead to an event exceeding its capacity.
 *
 * @package Drupal\rng\Exception
 *
 * Defines an exception when a user attempts to add registrants to a
 * registration that cannot accept more.
 */
class MaxRegistrantsExceededException extends \Exception {
}
