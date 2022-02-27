<?php

namespace Drupal\easy_email\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\easy_email\Entity\EasyEmailInterface;
use Drupal\easy_email\Event\EasyEmailEvent;
use Drupal\easy_email\Event\EasyEmailEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EmailUserEvaluator implements EmailUserEvaluatorInterface {

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * EmailUserEvaluator constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EventDispatcherInterface $eventDispatcher, EntityTypeManagerInterface $entityTypeManager) {
    $this->eventDispatcher = $eventDispatcher;
    $this->entityTypeManager = $entityTypeManager;
    $this->userStorage = $entityTypeManager->getStorage('user');
  }


  /**
   * @inheritDoc
   */
  public function evaluateUsers(EasyEmailInterface $email) {
    $this->eventDispatcher->dispatch(EasyEmailEvents::EMAIL_PREUSEREVAL, new EasyEmailEvent($email));

    if ($email->hasField('recipient_uid')) {
      $recipients = $email->getRecipientAddresses();
      if (!empty($recipients)) {
        $results = $this->userStorage->getQuery()
          ->condition('mail', $recipients, 'IN')
          ->execute();
        if (!empty($results)) {
          $email->setRecipientIds(array_keys($results));
        }
      }
    }

    if ($email->hasField('cc_uid')) {
      $cc = $email->getCCAddresses();
      if (!empty($cc)) {
        $results = $this->userStorage->getQuery()
          ->condition('mail', $cc, 'IN')
          ->execute();
        if (!empty($results)) {
          $email->setCCIds(array_keys($results));
        }
      }
    }

    if ($email->hasField('bcc_uid')) {
      $bcc = $email->getBCCAddresses();
      if (!empty($bcc)) {
        $results = $this->userStorage->getQuery()
          ->condition('mail', $bcc, 'IN')
          ->execute();
        if (!empty($results)) {
          $email->setBCCIds(array_keys($results));
        }
      }
    }

    $this->eventDispatcher->dispatch(EasyEmailEvents::EMAIL_USEREVAL, new EasyEmailEvent($email));
  }


}