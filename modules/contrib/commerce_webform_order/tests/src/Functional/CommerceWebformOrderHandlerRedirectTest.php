<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

/**
 * Tests Commerce Webform Order handler: Redirect.
 *
 * @group commerce_webform_order
 */
class CommerceWebformOrderHandlerRedirectTest extends CommerceWebformOrderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_webform_order_test_redirect',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $testWebforms = [
    'cwo_test_redirect_1',
    'cwo_test_redirect_2',
  ];

  /**
   * Redirect is disabled.
   *
   * In this test we are going to check the redirect feature when it is
   * disabled:
   *   - After submit, there is't any redirection.
   */
  public function testRedirectDisabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $webform_submission = $this->postProductVariationToWebform('ONE', 'cwo_test_redirect_1');

    // A status message isn't displayed with the redirect message.
    $this->assertUrl($webform_submission->getWebform()->getSetting('page_submit_path'));
  }

  /**
   * Redirect is enabled.
   *
   * In this test we are going to check the redirect feature when it is enabled:
   *   - After submit, the user is redirected to the checkout.
   */
  public function testRedirectEnabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $webform_submission = $this->postProductVariationToWebform('ONE', 'cwo_test_redirect_2');
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $this->orderItemRepository->getLastByWebformSubmission($webform_submission, 'commerce_webform_order_handler');

    // A status message isn't displayed with the redirect message.
    $this->assertUrl(sprintf('/checkout/%s/login', $order_item->getOrderId()));
  }

}
