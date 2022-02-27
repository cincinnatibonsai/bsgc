<?php

namespace Drupal\block_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a test block with some text.
 * 
 * @Block(
 *  id = "simple_test_block"),
 *  admin_label = @Translation("Simple Test Block")
 * )
 */
 class ExampleBlock extends BlockBase {
     
     /**
      * {@inheritdoc}
      */
      public function build() {
          return [
              '#type' => 'markup',
              '#markup' => 'This is a drupal 9 custom block.',
              ];
      }
     
 }