<?php

namespace Drupal\rng_easy_email\EventSubscriber;

use Drupal\rng\Event\RegistrationEvent;
use Drupal\rng_easy_email\DispatchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\rng\EventManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RegistrationSubscriber.
 */
class RegistrationSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\rng\EventManagerInterface definition.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $rngEventManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Dispatch Service.
   *
   * @var \Drupal\rng_easy_email\DispatchService
   */
  protected $dispatchService;

  /**
   * Constructs a new RegistrationSubscriber object.
   */
  public function __construct(EventManagerInterface $rng_event_manager, EntityTypeManagerInterface $entity_type_manager, DispatchService $dispatchService) {
    $this->rngEventManager = $rng_event_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->dispatchService = $dispatchService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['rng.registration.insert'] = ['registrationSend'];
    $events['rng.registration.registration'] = ['registrationSend'];

    return $events;
  }

  /**
   * Sends mail in case the registration is confirmed.
   *
   * This method is called when the following events are dispatched:
   * - rng.registration.insert;
   * - rng.registration.registration.
   *
   * @param \Drupal\rng\Event\RegistrationEvent $event
   *   The dispatched event.
   */
  public function registrationSend(RegistrationEvent $event) {
    $registration = $event->getRegistration();
    if ($registration->isConfirmed()) {
      $this->dispatchService->sendRegistration('attendee_registered', $registration);
    }
  }

}
