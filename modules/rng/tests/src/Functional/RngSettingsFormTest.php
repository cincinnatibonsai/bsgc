<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\Core\Url;

/**
 * Tests RNG settings form.
 *
 * @group rng
 */
class RngSettingsFormTest extends RngBrowserTestBase {

  /**
   * Test settings form menu link.
   */
  public function testSettingsMenuLink() {
    $web_user = $this->drupalCreateUser([
      'administer rng',
      'access administration pages',
    ]);
    $this->drupalLogin($web_user);

    $this->drupalGet('admin/config');
    $this->assertSession()->linkByHrefExists(Url::fromRoute('rng.config.settings')->toString());
  }

  /**
   * Test settings form.
   */
  public function testSettingsForm() {
    $session = $this->assertSession();
    $web_user = $this->drupalCreateUser(['administer rng']);
    $this->drupalLogin($web_user);

    $this->drupalGet(Url::fromRoute('rng.config.settings'));
    $session->responseContains('Enable people types who can register for events.');
    $this->assertTrue(in_array('user', $this->config('rng.settings')->get('identity_types')), 'Registrant types install config contains user registrant pre-enabled.');
    $session->checkboxChecked('edit-contactables-user');

    $edit = ['contactables[user]' => FALSE];
    $this->submitForm($edit, 'Save configuration');
    $session->responseContains('RNG settings updated.');
    $session->checkboxNotChecked('edit-contactables-user');

    $this->assertCount(0, $this->config('rng.settings')->get('identity_types'), 'All identity types disabled and saved to config.');
  }

}
