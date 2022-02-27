<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

use Drupal\Core\Database\Database;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Commerce Webform Order handler: Bypass access.
 *
 * @group commerce_webform_order
 */
class CommerceWebformOrderHandlerBypassAccessTest extends CommerceWebformOrderTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'dblog',
    'commerce_webform_order_test_bypass_access',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $testWebforms = [
    'cwo_test_bypass_access_1',
    'cwo_test_bypass_access_2',
  ];

  /**
   * Bypass access is disabled.
   *
   * In this test we are going to check the bypass access feature when it is
   * disabled:
   *   - A drupal status message is displayed instead of throwing an exception.
   *   - A more detailed log message is created.
   */
  public function testBypassAccessDisabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $this->postProductVariationToWebform('ONE', 'cwo_test_bypass_access_1');

    // The added message is not displayed and an error status message is
    // displayed.
    $this->assertSession()->pageTextNotContains('1 item');
    $this->assertSession()->pageTextContains('There was a problem processing your request. Please, try again.');

    // Check the last watchdog error message.
    $message = Database::getConnection()
      ->queryRange(
        "SELECT message FROM {watchdog} WHERE type = :type ORDER BY wid DESC",
        0,
        1,
        [':type' => 'commerce_webform_order']
      )->fetchField();
    $this->assertEqual('Unable to load the specified Commerce Store, please, try to fix the user permissions to view stores or enable bypass access control under store setting of the handler.', $message);
  }

  /**
   * Bypass access is enabled.
   *
   * In this test we are going to check the bypass access feature when it is
   * enabled:
   *   - A drupal status message isn't displayed.
   */
  public function testBypassAccessEnabled() {
    // Test as anonymous user.
    $this->drupalLogout();

    $this->postProductVariationToWebform('ONE', 'cwo_test_bypass_access_2');

    // The added message is displayed and an error status message is not
    // displayed.
    $this->assertSession()->pageTextContains('1 item');
    $this->assertSession()->pageTextNotContains('There was a problem processing your request. Please, try again.');
  }

}
