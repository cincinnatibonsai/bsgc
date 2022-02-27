<?php

namespace Drupal\rng\EventSubscriber;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rng\Event\RegistrationEvent;
use Drupal\rng\Event\RegistrationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\rng\EventManagerInterface;

/**
 * Reacts to registration inserts.
 *
 * Acts if events are configured to allow wait lists.
 */
class RegistrationWaitListSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Drupal\rng\EventManagerInterface definition.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $rngEventManager;

  /**
   * RegistrationWaitListSubscriber constructor.
   *
   * @param \Drupal\rng\EventManagerInterface $rng_event_manager
   *   The event manager.
   */
  public function __construct(EventManagerInterface $rng_event_manager) {
    $this->rngEventManager = $rng_event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RegistrationEvents::REGISTRATION_INSERT] = [
      'onRegistrationInsert',
      -1,
    ];
    return $events;
  }

  /**
   * Notify the user if they are added to a wait list.
   *
   * @param \Drupal\rng\Event\RegistrationEvent $event
   *   The event.
   *
   * @throws \Drupal\rng\Exception\InvalidEventException
   */
  public function onRegistrationInsert(RegistrationEvent $event) {
    $meta = $this->rngEventManager->getMeta($event->getRegistration()->getEvent());
    if ($meta->allowWaitList() && !$meta->hasCapacity()) {
      $this->messenger()->addStatus($this->t('Registration is at its capacity. You have been added to a waiting list.'));
    }
  }

}
