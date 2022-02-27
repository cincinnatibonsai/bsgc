<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\rng\Entity\Rule;
use Drupal\rng\Entity\RuleComponent;
use Drupal\rng\Entity\EventTypeRule;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\Form\EventTypeForm;

/**
 * Tests manage event access page.
 *
 * @group rng
 */
class RngEventAccessWebTest extends RngBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test', 'block'];

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * A registration type for testing.
   *
   * @var \Drupal\rng\Entity\RegistrationTypeInterface
   */
  protected $registrationType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->registrationType = $this->createRegistrationType();
    $this->createEventType('entity_test', 'entity_test');

    $this->container->get('router.builder')->rebuildIfNeeded();
    $this->container->get('plugin.manager.menu.local_action')->clearCachedDefinitions();

    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Test event access page when using site defaults.
   */
  public function testEventAccessSiteDefaults() {
    EventTypeForm::createDefaultRules('entity_test', 'entity_test');
    $user1 = $this->drupalCreateUser([
      'view test entity',
      'administer entity_test content',
    ]);
    $this->drupalLogin($user1);

    $event_meta = $this->createEventMeta(['user_id' => $user1->id()]);

    $this->drupalGet(Url::fromRoute('rng.event.entity_test.access', [
      'entity_test' => $event_meta->getEvent()->id(),
    ]));

    // Reset access rules button.
    $reset_link = Url::fromRoute('rng.event.entity_test.access.reset', [
      'entity_test' => $event_meta->getEvent()->id(),
    ]);
    $this->assertSession()->linkExists('Customize access rules');
    $this->assertSession()->linkByHrefExists($reset_link->toString());

    // Check if one of the checkboxes is disabled.
    $field_name = 'table[6][operation_create][enabled]';
    $this->assertSession()->fieldExists($field_name);
    $input = $this->xpath('//input[@name="' . $field_name . '" and @disabled="disabled"]');
    $this->assertTrue(count($input) === 1, 'The create checkbox is disabled.');
  }

  /**
   * Test event access page when using custom rules.
   */
  public function testEventAccessCustomized() {
    EventTypeForm::createDefaultRules('entity_test', 'entity_test');
    $user1 = $this->drupalCreateUser([
      'view test entity',
      'administer entity_test content',
    ]);
    $this->drupalLogin($user1);

    $event_meta = $this->createEventMeta(['user_id' => $user1->id()]);
    $event_meta->addDefaultAccess();

    $this->drupalGet(Url::fromRoute('rng.event.entity_test.access', [
      'entity_test' => $event_meta->getEvent()->id(),
    ]));

    // Reset access rules button.
    $reset_link = Url::fromRoute('rng.event.entity_test.access.reset', [
      'entity_test' => $event_meta->getEvent()->id(),
    ]);
    $this->assertSession()->linkExists('Reset access rules to site default');
    $this->assertSession()->linkByHrefExists($reset_link->toString());

    // Check if one of the checkboxes is enabled.
    $field_name = 'table[6][operation_create][enabled]';
    $this->assertSession()->fieldExists($field_name);
    $input = $this->xpath('//input[@name="' . $field_name . '" and @disabled="disabled"]');
    $this->assertTrue(count($input) === 0, 'The create checkbox is not disabled.');
  }

  /**
   * Tests deleting all overridden rules for events in the UI.
   */
  public function testDeleteAllCustomRules() {
    $rule_storage = $this->container->get('entity_type.manager')->getStorage('rng_rule');
    EventTypeForm::createDefaultRules('entity_test', 'entity_test');

    // Create event and override rules.
    $event_meta = $this->createEventMeta();
    $event_meta->addDefaultAccess();

    // Assert that three rules exist now.
    $rules = $rule_storage->loadMultiple();
    $this->assertCount(3, $rules);

    // Login as admin and go the page for deleting all custom rules.
    $admin = $this->drupalCreateUser(['administer event types']);
    $this->drupalLogin($admin);
    $this->drupalGet(Url::fromRoute('entity.rng_event_type.access_defaults.delete_all', [
      'rng_event_type' => 'entity_test.entity_test',
    ]));
    $this->submitForm([], 'Delete all existing access rules');

    // Assert that no custom rules exist anymore.
    $rule_storage->resetCache();
    $rules = $rule_storage->loadMultiple();
    $this->assertCount(0, $rules);

    // Assert message in the UI.
    $this->assertSession()->pageTextContains('3 custom access rules deleted.');
  }

  /**
   * Test access from event rules.
   *
   * Ensure if these rules change they invalidate caches.
   */
  public function testComponentAccessCache() {
    $session = $this->assertSession();

    $event = EntityTest::create([
      EventManagerInterface::FIELD_REGISTRATION_TYPE => $this->registrationType->id(),
      EventManagerInterface::FIELD_STATUS => TRUE,
    ]);
    $event->save();

    $register_link = Url::fromRoute('rng.event.entity_test.register.type_list', [
      'entity_test' => $event->id(),
    ]);
    $register_link_str = $register_link->toString();

    $event_meta = $this->eventManager->getMeta($event);
    $this->assertCount(0, $event_meta->getRules(NULL, FALSE, TRUE), 'There are zero rules');

    // Set rules via API to set a baseline.
    $rule = Rule::create([
      'event' => ['entity' => $event],
      'trigger_id' => 'rng_event.register',
      'status' => TRUE,
    ]);

    $component = RuleComponent::create()
      ->setType('condition')
      ->setPluginId('rng_user_role')
      ->setConfiguration(['roles' => []]);
    $rule->addComponent($component);

    $component = RuleComponent::create()
      ->setType('action')
      ->setPluginId('registration_operations')
      ->setConfiguration(['registration_operations' => ['create' => FALSE]]);
    $rule->addComponent($component);

    $rule->save();
    \Drupal::service('router.builder')->rebuildIfNeeded();

    $user_registrant = $this->drupalCreateUser([
      'rng register self',
      'view test entity',
      'administer entity_test content',
    ]);
    $roles = $user_registrant->getRoles(TRUE);

    $this->drupalLogin($user_registrant);
    $this->drupalGet($event->toUrl());
    $session->statusCodeEquals(200);
    // Register tab is cached, ensure it is missing.
    $this->assertSession()->linkByHrefNotExists($register_link_str);
    $this->drupalGet($register_link);
    $session->statusCodeEquals(403);

    $user_manager = $this->drupalCreateUser(['administer entity_test content']);
    $this->drupalLogin($user_manager);

    // Set conditions so registrant user can register
    // Use UI because component form should invalidate tags.
    $conditions = $rule->getConditions();
    $edit = ['roles[' . $roles[0] . ']' => TRUE];
    $this->drupalGet($conditions[0]->toUrl());
    $this->submitForm($edit, 'Save');
    $actions = $rule->getActions();
    $edit = ['operations[create]' => TRUE];
    $this->drupalGet($actions[0]->toUrl());
    $this->submitForm($edit, 'Save');

    $this->drupalLogin($user_registrant);
    $this->drupalGet($event->toUrl());
    $session->statusCodeEquals(200);
    // Register tab is cached, ensure it is exposed.
    // If this fails, then the register tab is still cached to previous rules.
    $session->linkByHrefExists($register_link_str);
    $this->drupalGet($register_link);
    $session->statusCodeEquals(200);
  }

  /**
   * Test access from event type rule defaults.
   *
   * Ensure if these rules change they invalidate caches.
   */
  public function testComponentAccessDefaultsCache() {
    $session = $this->assertSession();

    // Create a rule as a baseline.
    $rule = EventTypeRule::create([
      'trigger' => 'rng_event.register',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'machine_name' => 'user_role',
    ]);
    $rule->setCondition('role', [
      'id' => 'rng_user_role',
      'roles' => [],
    ]);
    $rule->setAction('registration_operations', [
      'id' => 'registration_operations',
      'configuration' => [
        'operations' => [],
      ],
    ]);
    $rule->save();

    $event = EntityTest::create([
      EventManagerInterface::FIELD_REGISTRATION_TYPE => $this->registrationType->id(),
      EventManagerInterface::FIELD_STATUS => TRUE,
    ]);
    $event->save();

    $register_link = Url::fromRoute('rng.event.entity_test.register.type_list', [
      'entity_test' => $event->id(),
    ]);
    $register_link_str = $register_link->toString();

    $user_registrant = $this->drupalCreateUser([
      'rng register self',
      'view test entity',
      'administer entity_test content',
    ]);
    $this->drupalLogin($user_registrant);

    $this->drupalGet($event->toUrl());
    $session->statusCodeEquals(200);
    $session->linkByHrefNotExists($register_link_str);
    $this->drupalGet($register_link);
    $session->statusCodeEquals(403);

    $admin = $this->drupalCreateUser(['administer event types']);
    $this->drupalLogin($admin);

    $edit['actions[operations][user_role][create]'] = TRUE;
    $this->drupalGet('/admin/structure/rng/event_types/manage/entity_test.entity_test/access_defaults');
    $this->submitForm($edit, 'Save');

    $this->drupalLogin($user_registrant);
    $this->drupalGet($event->toUrl());
    $session->statusCodeEquals(200);
    $session->linkByHrefExists($register_link_str);
    $this->drupalGet($register_link);
    $session->statusCodeEquals(200);
  }

}
