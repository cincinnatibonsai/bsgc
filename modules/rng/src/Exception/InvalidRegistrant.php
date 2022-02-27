<?php

namespace Drupal\rng\Exception;

/**
 * Thrown when trying to create a registrant that is missing context.
 *
 * Examples of missing context:
 * - For the registrant no registration entity is referenced;
 * - For the registrant no identity entity is provided in cases where this is
 *   required.
 *
 * @package Drupal\rng\Exception
 */
class InvalidRegistrant extends \Exception {
}
