<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

/**
 * Tests Commerce Webform Order handler: Order item ID.
 *
 * @group commerce_webform_order
 */
class CommerceWebformOrderHandlerOrderItemIdTest extends CommerceWebformOrderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_webform_order_test_order_item_id',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $testWebforms = [
    'cwo_test_order_item_id',
  ];

  /**
   * Order item ID is stored in an element value.
   */
  public function testCurrentOrderItemId() {
    // Test as admin user.
    $webform_submission = $this->postProductVariationToWebform('ONE', 'cwo_test_order_item_id');
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $this->orderItemRepository->getLastByWebformSubmission($webform_submission, 'commerce_webform_order_handler');

    // The order item ID is stored in the configured element.
    $this->assertEqual($order_item->id(), $webform_submission->getElementData('order_item_id'));
  }

}
