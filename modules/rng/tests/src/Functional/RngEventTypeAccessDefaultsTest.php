<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\Core\Url;

/**
 * Tests event type access defaults.
 *
 * @group rng
 */
class RngEventTypeAccessDefaultsTest extends RngBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $admin = $this->drupalCreateUser(['administer event types']);
    $this->drupalLogin($admin);
  }

  /**
   * Test access defaults.
   */
  public function testAccessDefaults() {
    $session = $this->assertSession();
    $edit = [
      'bundle' => 'entity_test.entity_test',
      'registrants[registrant_type]' => 'registrant',
    ];
    $this->drupalGet(Url::fromRoute('entity.rng_event_type.add'));
    $this->submitForm($edit, 'Save');

    $defaults_route = Url::fromRoute('entity.rng_event_type.access_defaults', [
      'rng_event_type' => 'entity_test.entity_test',
    ]);
    $this->drupalGet($defaults_route);

    // Ensure checkboxes have default values.
    $this->assertNoFieldById('edit-actions-operations-event-manager-create');
    $session->checkboxChecked('edit-actions-operations-event-manager-view');
    $session->checkboxChecked('edit-actions-operations-event-manager-update');
    $session->checkboxChecked('edit-actions-operations-event-manager-delete');

    $this->assertNoFieldById('edit-actions-operations-registrant-create');
    $session->checkboxChecked('edit-actions-operations-registrant-view');
    $session->checkboxChecked('edit-actions-operations-registrant-update');
    $session->checkboxNotChecked('edit-actions-operations-registrant-delete');

    $session->checkboxChecked('edit-actions-operations-user-role-create');
    $session->checkboxNotChecked('edit-actions-operations-user-role-view');
    $session->checkboxNotChecked('edit-actions-operations-user-role-update');
    $session->checkboxNotChecked('edit-actions-operations-user-role-delete');

    $edit = [
      'actions[operations][user_role][delete]' => TRUE,
    ];
    $this->drupalGet($defaults_route);
    $this->submitForm($edit, 'Save');

    $this->assertSession()->responseContains('Event type access defaults saved.');
    // Update field still unchecked.
    $session->checkboxNotChecked('edit-actions-operations-user-role-update');
    // Delete field is now checked.
    $session->checkboxChecked('edit-actions-operations-user-role-delete');
  }

}
