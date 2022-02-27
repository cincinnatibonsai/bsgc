<?php

namespace Drupal\Tests\rng\Kernel;

use Drupal\rng\EventManagerInterface;
use Drupal\rng\EventMetaInterface;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\UserInterface;

/**
 * Tests ability to register for events..
 *
 * @group rng
 */
class RngRegistrationAccessTest extends RngKernelTestBase {

  use UserCreationTrait {
    createUser as drupalCreateUser;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rng',
    'user',
    'field',
    'dynamic_entity_reference',
    'unlimited_number',
    'courier',
    'text',
    'entity_test',
    'system',
  ];

  /**
   * An config entity of type "registration_type".
   *
   * @var \Drupal\rng\RegistrationTypeInterface
   */
  protected $registrationType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setupRngRules();
    $this->installEntitySchema('user');
    $this->installSchema('system', ['sequences']);

    $this->registrationType = $this->createRegistrationType();
    $this->setupEventType();
  }

  /**
   * Test register self.
   */
  public function testRegisterSelf() {
    $event_meta = $this->createEventMeta();
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);
    $this->createUserRoleRules([], ['create' => TRUE]);
    $this->assertUserCanRegister($user1, $event_meta);
  }

  /**
   * Test register self with no default rules.
   */
  public function testRegisterSelfNoDefaultRules() {
    $event_meta = $this->createEventMeta();
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);
    $this->assertUserCanNotRegister($user1, $event_meta);
  }

  /**
   * Test register self rule with no roles.
   *
   * No roles = All roles.
   */
  public function testRegisterSelfRuleNoRoles() {
    $event_meta = $this->createEventMeta();
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);
    $this->createUserRoleRules([], ['create' => TRUE]);
    $this->assertUserCanRegister($user1, $event_meta);
  }

  /**
   * Test register self rule a role the user does not have.
   */
  public function testRegisterSelfRuleRoleAlternative() {
    $event_meta = $this->createEventMeta();
    $role1 = $this->createRole([]);
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);
    $this->createUserRoleRules([$role1 => $role1], ['create' => TRUE]);
    $this->assertUserCanNotRegister($user1, $event_meta);
  }

  /**
   * Test register self no permission.
   */
  public function testRegisterSelfNoPermission() {
    $event_meta = $this->createEventMeta();
    // Need to create a dummy role otherwise 'authenticated' is used.
    $role1 = $this->createRole([]);
    $user1 = $this->drupalCreateUser();
    $this->setCurrentUser($user1);
    $this->createUserRoleRules([$role1 => $role1], ['create' => TRUE]);
    $this->assertUserCanNotRegister($user1, $event_meta);
  }

  /**
   * Test register self no duplicates.
   */
  public function testRegisterSelfNoDuplicates() {
    $event_meta = $this->createEventMeta([
      EventManagerInterface::FIELD_ALLOW_DUPLICATE_REGISTRANTS => 0,
    ]);
    $this->createUserRoleRules([], ['create' => TRUE]);
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);

    $this->assertUserCanRegister($user1, $event_meta);
    $this->createRegistration($event_meta->getEvent(), $this->registrationType, [$user1]);
    $this->assertUserCanNotRegister($user1, $event_meta);
  }

  /**
   * Test register self duplicates allowed.
   */
  public function testRegisterSelfWithDuplicates() {
    $event_meta = $this->createEventMeta([
      EventManagerInterface::FIELD_ALLOW_DUPLICATE_REGISTRANTS => 1,
    ]);

    $this->createUserRoleRules([], ['create' => TRUE]);
    $user1 = $this->drupalCreateUser(['rng register self']);
    $this->setCurrentUser($user1);

    $this->assertUserCanRegister($user1, $event_meta);
    $this->createRegistration($event_meta->getEvent(), $this->registrationType, [$user1]);
    $this->assertUserCanRegister($user1, $event_meta);
  }

  /**
   * Asserts that an user can register for event.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity for which to check if it can register.
   * @param \Drupal\rng\EventMetaInterface $event_meta
   *   The meta event wrapper.
   */
  protected function assertUserCanRegister(UserInterface $user, EventMetaInterface $event_meta) {
    $user_id = $user->id();
    $this->assertEquals([$user_id => $user_id], $event_meta->identitiesCanRegister('user', [$user_id]));
  }

  /**
   * Asserts that an user cannot register for event.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity for which to check if it can register.
   * @param \Drupal\rng\EventMetaInterface $event_meta
   *   The meta event wrapper.
   */
  protected function assertUserCannotRegister(UserInterface $user, EventMetaInterface $event_meta) {
    $user_id = $user->id();
    $this->assertEquals([], $event_meta->identitiesCanRegister('user', [$user_id]));
  }

}
