<?php

namespace Drupal\Tests\rng\Functional;

use Drupal\rng\Form\EventTypeForm;

/**
 * Sets up page and article content types.
 */
abstract class RngSiteTestBase extends RngBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['rng', 'node'];

  /**
   * An config entity of type "registration_type".
   *
   * @var \Drupal\rng\RegistrationTypeInterface
   */
  protected $registrationType;

  /**
   * A content type.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $eventBundle;

  /**
   * An event type entity.
   *
   * @var \Drupal\rng\Entity\EventTypeInterface
   */
  protected $eventType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->eventBundle = $this->drupalCreateContentType();
    $this->eventType = $this->createEventType('node', $this->eventBundle->id());
    EventTypeForm::createDefaultRules('node', $this->eventBundle->id());
    $this->registrationType = $this->createRegistrationType();
    $this->container->get('router.builder')->rebuildIfNeeded();
  }

}
