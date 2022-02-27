<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\Core\Url;
use Drupal\rng\Entity\RegistrationType;

/**
 * Tests registration types.
 *
 * @group rng
 */
class RngRegistrationTypeTest extends RngSiteTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block'];

  /**
   * An event entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $event;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $bundle = $this->eventBundle->id();
    $account = $this->drupalCreateUser(['edit own ' . $bundle . ' content']);
    $this->drupalLogin($account);

    $this->event = $this->createEventNode($this->eventBundle, [
      'uid' => \Drupal::currentUser()->id(),
    ]);

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Test registration types in UI.
   */
  public function testRegistrationTypes() {
    $web_user = $this->drupalCreateUser([
      'administer registration types',
      'access administration pages',
    ]);
    $this->drupalLogin($web_user);

    // Create and delete the testing registration type.
    $this->drupalGet('admin/structure/rng/registration_types/manage/' . $this->registrationType->id());
    $this->registrationType->delete();

    // Administration.
    $this->drupalGet('admin/structure/rng');
    $session = $this->assertSession();
    $session->linkByHrefExists(Url::fromRoute('rng.registration_type.overview')->toString());

    $this->drupalGet('admin/structure/rng/registration_types');
    $this->assertSession()->responseContains('No registration types found.');
    $this->assertCount(0, RegistrationType::loadMultiple());

    // Local action.
    $session->linkByHrefExists(Url::fromRoute('entity.registration_type.add')->toString());

    // Add.
    $edit = ['label' => 'Foobar1', 'id' => 'foobar'];
    $this->drupalGet('admin/structure/rng/registration_types/add');
    $this->submitForm($edit, 'Save');
    $session->responseContains(t('%label registration type was added.', ['%label' => 'Foobar1']));
    $this->assertCount(1, RegistrationType::loadMultiple());

    // Registration type list.
    $this->assertSession()->addressEquals(Url::fromRoute('rng.registration_type.overview', [], ['absolute' => TRUE])->toString());
    $this->assertSession()->responseContains('<td>Foobar1</td>');

    // Edit.
    $edit = ['label' => 'Foobar2'];
    $this->drupalGet('admin/structure/rng/registration_types/manage/foobar');
    $this->submitForm($edit, 'Save');
    $session->responseContains(t('%label registration type was updated.', ['%label' => 'Foobar2']));

    $registration_type = RegistrationType::load('foobar');
    $registration[0] = $this->createRegistration($this->event, $registration_type, []);
    $registration[1] = $this->createRegistration($this->event, $registration_type, []);

    $this->drupalGet('admin/structure/rng/registration_types/manage/foobar/delete');
    $session->responseContains(\Drupal::translation()->formatPlural(
      count($registration),
      'Unable to delete registration type. It is used by @count registration.',
      'Unable to delete registration type. It is used by @count registrations.'
    ));

    $registration[0]->delete();
    $registration[1]->delete();

    // No registrations; delete is allowed.
    $this->drupalGet('admin/structure/rng/registration_types/manage/foobar/delete');
    $session->responseContains('This action cannot be undone.');

    // Delete.
    $this->drupalGet('admin/structure/rng/registration_types/manage/foobar/delete');
    $this->submitForm([], 'Delete');
    $session->responseContains(t('Registration type %label was deleted.', ['%label' => 'Foobar2']));
    $this->assertCount(0, RegistrationType::loadMultiple(), 'Registration type entity removed from storage.');
  }

}
