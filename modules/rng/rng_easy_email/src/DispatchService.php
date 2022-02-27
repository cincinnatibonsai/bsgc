<?php

namespace Drupal\rng_easy_email;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\easy_email\Service\EmailHandlerInterface;
use Drupal\rng\Entity\RegistrantInterface;
use Drupal\rng\Entity\RegistrationInterface;

/**
 * Class DispatchService.
 */
class DispatchService {

  /**
   * Drupal\easy_email\Service\EmailHandlerInterface definition.
   *
   * @var \Drupal\easy_email\Service\EmailHandlerInterface
   */
  protected $easyEmailHandler;

  /**
   * Constructs a new DispatchService object.
   */
  public function __construct(EmailHandlerInterface $easy_email_handler) {
    $this->easyEmailHandler = $easy_email_handler;
  }

  /**
   *
   */
  public function send($template, RegistrantInterface $registrant) {
    $email = $this->easyEmailHandler->createEmail([
      'type' => $template,
      'field_registrant' => $registrant->id(),
      'field_registration' => $registrant->getRegistration()->id(),
      'field_event' => $registrant->getEvent()->id(),
    ]);
    if (!$this->easyEmailHandler->duplicateExists($email)) {
      $this->easyEmailHandler->sendEmail($email);
    }
  }

  /**
   *
   */
  public function sendRegistration($template, RegistrationInterface $registration) {
    // Don't send for events in the past, to allow import of old registrants.
    $event = $registration->getEventMeta();
    if ($event->isPastEvent()) {
      return;
    }
    foreach ($registration->getRegistrants() as $registrant) {
      $this->send($template, $registrant);
    }
  }

  /**
   *
   */
  public function sendEvent($template, ContentEntityInterface $contentEntity) {

  }

}
