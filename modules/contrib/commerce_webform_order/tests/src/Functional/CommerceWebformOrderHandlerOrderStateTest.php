<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

/**
 * Tests Commerce Webform Order handler: Order state.
 *
 * @group commerce_webform_order
 */
class CommerceWebformOrderHandlerOrderStateTest extends CommerceWebformOrderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_webform_order_test_order_state',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $testWebforms = [
    'cwo_test_order_state_1',
    'cwo_test_order_state_2',
  ];

  /**
   * Default order state.
   *
   * In this test we are going to check the default order state:
   *   - After submit, the order is a cart.
   */
  public function testDefatultOrderState() {
    $webform_submission = clone ($this->postProductVariationToWebform('ONE', 'cwo_test_order_state_1'));
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = clone ($this->orderItemRepository->getLastByWebformSubmission($webform_submission, 'commerce_webform_order_handler'));
    $order = $order_item->getOrder();

    // Check order state.
    $this->assertEqual($order->getState()->getId(), 'draft');
  }

  /**
   * Custom order state.
   *
   * In this test we are going to check a custom order state:
   *   - After submit, the order is completed.
   */
  public function testCustomOrderState() {
    $webform_submission = clone ($this->postProductVariationToWebform('ONE', 'cwo_test_order_state_2'));
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = clone ($this->orderItemRepository->getLastByWebformSubmission($webform_submission, 'commerce_webform_order_handler'));
    $order = $order_item->getOrder();

    // Check order state.
    $this->assertEqual($order->getState()->getId(), 'completed');
  }

}
