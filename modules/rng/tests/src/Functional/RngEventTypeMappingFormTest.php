<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\rng\EventManagerInterface;

/**
 * Tests RNG event type mapping form.
 *
 * @group rng
 */
class RngEventTypeMappingFormTest extends RngBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test'];

  /**
   * The event type for testing.
   *
   * @var \Drupal\rng\Entity\EventTypeInterface
   */
  public $eventType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $user = $this->drupalCreateUser(['administer event types']);
    $this->drupalLogin($user);
    $this->eventType = $this->createEventType('entity_test', 'entity_test');
  }

  /**
   * Test default state of the mapping form with a fresh event type.
   */
  public function testMappingFormDefaultState() {
    // Go to the field mapping page of the event type.
    $this->drupalGet($this->eventType->toUrl('field-mapping'));

    $table = $this->parseTable();
    $expected_table = [
      'Registration type|Select which registration types are valid for this event.|Exists',
      'Registration groups|New registrations will be added to these groups.|Exists',
      'Accept new registrations|Accept new registrations for this event.|Exists',
      'Allow a wait list|Allow a waiting list for the event.|Exists',
      'Maximum registrants|Maximum amount of registrants for this event.|Exists',
      'Capacity based on confirmed registrations|When nearing capacity, do unconfirmed registrations count towards the used capacity, or only confirmed registrations?|Exists',
      'Reply-to e-mail address|E-mail address that appears as reply-to when emails are sent from this event. Leave empty to use site default.|Exists',
      'Allow duplicate registrants|Allows a registrant to create more than one registration for this event.|Exists',
    ];
    foreach ($expected_table as $row) {
      $this->assertStringContainsString($row, $table);
    }
  }

  /**
   * Test mapping form when a field does not exist.
   */
  public function testMappingFormDeleted() {
    // Delete one of the event fields on the event entity type.
    $field = FieldConfig::loadByName('entity_test', 'entity_test', EventManagerInterface::FIELD_REGISTRATION_TYPE);
    $field->delete();

    // Go to the field mapping page of the event type.
    $this->drupalGet($this->eventType->toUrl('field-mapping'));

    // Assert that the expected field is reported missing and that a 'Create'
    // button exists alongside of it.
    $this->assertStringContainsString('Registration type|Select which registration types are valid for this event.|Does not exist', $this->parseTable());
    $button = $this->assertSession()->buttonExists('edit-table-rng-registration-type-operations-create');

    // Click the button and assert that the field is added back.
    $button->click();
    $this->assertStringContainsString('Registration type|Select which registration types are valid for this event.|Exists', $this->parseTable());
    $this->assertSession()->pageTextContains('Field Registration type added.');
  }

  /**
   * Convert table to pipe delimited list.
   *
   * @return string
   *   A table in simple text form.
   */
  protected function parseTable(): string {
    $page = $this->getSession()->getPage();
    $rows = $page->findAll('css', 'table tbody tr');
    $table = [];
    foreach ($rows as $row) {
      $cells = $row->findAll('css', 'td');
      $cell_values = [];
      foreach ($cells as $cell) {
        $cell_values[] = $cell->getText();
      }
      $table[] = implode('|', $cell_values);
    }

    return implode("\n", $table);
  }

}
