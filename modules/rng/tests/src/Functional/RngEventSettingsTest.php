<?php

namespace Drupal\Tests\rng\Functional;

/**
 * Tests event settings page.
 *
 * @group rng
 */
class RngEventSettingsTest extends RngSiteTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Create two bundles of the same entity type, one bundle is an event type.
   *
   * Check if entities of each bundle are events.
   */
  public function testEvent() {
    $session = $this->assertSession();

    $bundle[0] = $this->eventBundle;
    $bundle[1] = $this->drupalCreateContentType();
    $bundle[2] = $this->drupalCreateContentType();
    $event_types[0] = $this->eventType;
    $event_types[1] = $this->createEventType('node', $bundle[2]->id());

    \Drupal::service('router.builder')->rebuildIfNeeded();

    $account = $this->drupalCreateUser([
      'edit own ' . $bundle[0]->id() . ' content',
      'edit own ' . $bundle[1]->id() . ' content',
    ]);
    $this->drupalLogin($account);

    $entity[0] = $this->createEventNode($bundle[0]);
    $entity[1] = $this->createEventNode($bundle[1]);

    $base_url = 'node/1';
    $this->drupalGet($base_url);
    $session->linkByHrefExists($base_url . '/event');
    $this->drupalGet($base_url . '/event');
    $session->statusCodeEquals(200);

    $base_url = 'node/2';
    $this->drupalGet($base_url);
    // Need for test for both existing and non existing links,
    // errors could show, and assertNoLink could be true.
    $session->linkByHrefExists($base_url);
    $session->linkByHrefNotExists($base_url . '/event');
    $this->drupalGet($base_url . '/event');
    $session->statusCodeEquals(403);

    // Ensure that after removing an event type, the Event links do not persist
    // for other entities of the same entity type, but different bundle.
    foreach ([403, 404] as $code) {
      $event_type = array_shift($event_types);
      $event_type->delete();
      \Drupal::service('router.builder')->rebuildIfNeeded();
      foreach (['node/1', 'node/2'] as $base_url) {
        $this->drupalGet($base_url . '/event');
        $session->statusCodeEquals($code);
        $this->drupalGet($base_url);
        $session->linkByHrefExists($base_url);
        $session->linkByHrefNotExists($base_url . '/event');
      }
    }
  }

  /**
   * Tests canonical event page, and the Event default local task.
   */
  public function testEventSettingsTabs() {
    $account = $this->drupalCreateUser([
      'edit own ' . $this->eventBundle->id() . ' content',
    ]);
    $this->drupalLogin($account);

    $event = $this->createEventNode($this->eventBundle);

    // Local task appears on canonical route.
    $base_url = 'node/1';
    $this->drupalGet($event->toUrl());
    $session = $this->assertSession();
    $session->linkByHrefExists($base_url . '/event');

    // Event settings form.
    $this->drupalGet('node/1/event');
    $session->linkExists('Settings');
    $session->linkByHrefExists($base_url . '/event/access');
    $session->linkByHrefExists($base_url . '/event/messages');
    $session->linkByHrefExists($base_url . '/event/groups');
  }

  /**
   * Tests changing event settings reveals the 'Register' tab.
   */
  public function testEventSettings() {
    $bundle = $this->eventBundle->id();
    $account = $this->drupalCreateUser([
      'access content',
      'edit own ' . $bundle . ' content',
      'rng register self',
    ]);
    $this->drupalLogin($account);

    $this->createEventNode($this->eventBundle, [
      'uid' => \Drupal::currentUser()->id(),
    ]);

    // Event.
    $base_url = 'node/1';
    $this->drupalGet($base_url . '');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $this->drupalGet($base_url . '/event');
    $session->statusCodeEquals(200);
    $session->linkByHrefNotExists($base_url . '/register');
    $this->drupalGet($base_url . '/register');
    $session->statusCodeEquals(403);

    // Settings.
    $edit = [
      'rng_status[value]' => TRUE,
      'rng_registration_type[' . $this->registrationType->id() . ']' => TRUE,
    ];
    $this->drupalGet($base_url . '/event');
    $this->submitForm($edit, 'Save');
    $session->responseContains('Event settings updated.');

    // Register tab appears.
    $session->linkByHrefExists($base_url . '/register');
  }

}
