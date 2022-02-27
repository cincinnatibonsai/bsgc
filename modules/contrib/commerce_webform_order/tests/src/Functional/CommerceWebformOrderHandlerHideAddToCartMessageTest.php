<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

/**
 * Tests Commerce Webform Order handler: Hide add to cart message.
 *
 * @group commerce_webform_order
 */
class CommerceWebformOrderHandlerHideAddToCartMessageTest extends CommerceWebformOrderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_webform_order_test_hide_cart_message',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $testWebforms = [
    'cwo_test_hide_cart_message_1',
    'cwo_test_hide_cart_message_2',
  ];

  /**
   * Hide add to cart message is disabled.
   *
   * In this test we are going to check the debug feature when it is disabled:
   *   - After submit, the add to cart message is displayed.
   */
  public function testHideAddToCartMessageDisabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $this->postProductVariationToWebform('ONE', 'cwo_test_hide_cart_message_1');

    // A status message is displayed with the cart message.
    $this->assertSession()->pageTextContains('added to your cart');
  }

  /**
   * Hide add to cart message is enabled.
   *
   * In this test we are going to check the debug feature when it is enabled:
   *   - After submit, the add to cart message isn't displayed.
   */
  public function testAddToCartMessageEnabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $this->postProductVariationToWebform('ONE', 'cwo_test_hide_cart_message_2');

    // A status message isn't displayed with the cart message.
    $this->assertSession()->pageTextNotContains('added to your cart');
  }

}
