<?php

namespace Drupal\Tests\courier\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Base kernel test.
 */
abstract class CourierKernelTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'dynamic_entity_reference',
    'filter',
    'field',
    'text',
    'courier',
  ];

}
