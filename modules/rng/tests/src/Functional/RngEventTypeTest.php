<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\rng\Entity\RngEventType;
use Drupal\courier\Entity\CourierContext;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Url;

/**
 * Tests event types.
 *
 * @group rng
 */
class RngEventTypeTest extends RngBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'field_ui', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Tests adding an event type in the UI.
   */
  public function testAddEventType() {
    $web_user = $this->drupalCreateUser([
      'administer event types',
      'access administration pages',
    ]);
    $this->drupalLogin($web_user);

    $this->drupalGet('/admin/structure/rng/event_types/add');
    $edit = [
      'registrants[registrant_type]' => 'registrant',
    ];
    $this->submitForm($edit, 'Save');

    // Assert that an event type was created.
    $this->assertSession()->pageTextContains('The content type Event has been added.');
    $this->assertCount(1, RngEventType::loadMultiple(), 'Event type exists in database.');
    $node_type = NodeType::load('event');
    $this->assertEquals('Event', $node_type->label());
  }

  /**
   * Tests adding an event type in the UI with dblog enabled.
   */
  public function testAddEventTypeWithDbLog() {
    $this->assertTrue($this->container->get('module_installer')->install(['dblog']));
    $this->testAddEventType();
  }

  /**
   * Test event types in UI.
   */
  public function testEventType() {
    $web_user = $this->drupalCreateUser([
      'administer event types',
      'access administration pages',
    ]);
    $this->drupalLogin($web_user);

    // Create and delete the testing event type.
    $event_bundle = $this->drupalCreateContentType();
    $event_type = $this->createEventType('node', $event_bundle->id());
    $this->drupalGet('admin/structure/rng/event_types/manage/' . $event_type->id() . '/edit');
    $event_type->delete();
    $event_bundle->delete();

    // Admin structure overview.
    $this->drupalGet('admin/structure');
    $session = $this->assertSession();
    $session->linkByHrefExists(Url::fromRoute('rng.structure')->toString());
    $this->assertSession()->responseContains('Manage registration entity types.');

    // Event types button on admin.
    $this->drupalGet('admin/structure/rng');
    $session->linkByHrefExists(Url::fromRoute('rng.rng_event_type.overview')->toString());
    $this->assertSession()->responseContains('Manage which entity bundles are designated as events.');

    // No events.
    $this->assertCount(0, RngEventType::loadMultiple(), 'There are no event type entities.');
    $this->drupalGet('admin/structure/rng/event_types');
    $this->assertSession()->responseContains('No event types found.');

    // There are no courier contexts.
    $this->assertCount(0, CourierContext::loadMultiple(), 'There are no courier context entities.');

    // Local action.
    $session = $this->assertSession();
    $session->linkByHrefExists(Url::fromRoute('entity.rng_event_type.add')->toString());

    // Add.
    $t_args = ['%label' => 'node.event'];
    $edit = [
      'registrants[registrant_type]' => 'registrant',
    ];
    $this->drupalGet('admin/structure/rng/event_types/add');
    $this->submitForm($edit, 'Save');

    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load('event');

    $this->assertCount(1, RngEventType::loadMultiple(), 'Event type exists in database.');

    $this->assertSession()->responseContains(t('The content type <a href=":url">%label</a> has been added.', [
      '%label' => $node_type->label(),
      ':url' => $node_type->toUrl()->toString(),
    ]));
    $this->assertSession()->responseContains(t('%label event type added.', $t_args));

    // Courier context created?
    $this->assertSession()->assert(CourierContext::load('rng_registration_node'), 'Courier context entity created for this event type\' entity type.');

    // Event type list.
    $this->drupalGet('admin/structure/rng/event_types');
    $this->assertSession()->responseContains('<td>Content: Event</td>');
    $options = ['node_type' => 'event'];
    $session->linkByHrefExists(Url::fromRoute("entity.node.field_ui_fields", $options)->toString());

    // Edit form.
    $edit = [];
    $this->drupalGet('admin/structure/rng/event_types/manage/node.event/edit');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains(t('%label event type updated.', $t_args));

    // Delete form.
    $this->drupalGet('admin/structure/rng/event_types/manage/node.event/delete');
    $this->assertSession()->responseContains('Are you sure you want to delete event type node.event?');

    $this->drupalGet('admin/structure/rng/event_types/manage/node.event/delete');
    $this->submitForm($edit, 'Delete');
    $this->assertSession()->responseContains(t('Event type %label was deleted.', $t_args));

    $this->assertCount(0, RngEventType::loadMultiple(), 'Event type deleted from database.');

    // @todo ensure conditional on form omits node/existing radios.
    // @todo create event type with custom entity.
  }

}
