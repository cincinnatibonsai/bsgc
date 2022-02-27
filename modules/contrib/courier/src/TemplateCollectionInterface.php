<?php

namespace Drupal\courier;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a courier_template_collection entity.
 */
interface TemplateCollectionInterface extends ContentEntityInterface, TokenInterface {

  /**
   * Gets the context entity.
   *
   * @return \Drupal\courier\CourierContextInterface|null
   *   The context entity, or NULL if it does not exist.
   */
  public function getContext();

  /**
   * Sets the context entity.
   *
   * @param \Drupal\courier\CourierContextInterface|null $entity
   *   A courier_context entity, or NULL to remove context.
   *
   * @return \Drupal\courier\TemplateCollectionInterface
   *   Return this object for chaining.
   */
  public function setContext(CourierContextInterface $entity);

  /**
   * Gets the owner entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The owner entity, or NULL if it does not exist.
   */
  public function getOwner();

  /**
   * Sets the owner entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   An entity, or NULL to set as global.
   *
   * @return \Drupal\courier\TemplateCollectionInterface
   *   Return this object for chaining.
   */
  public function setOwner(EntityInterface $entity);

  /**
   * Get template with a channel entity type.
   *
   * @param string $channel_type_id
   *   A channel entity type ID.
   *
   * @return \Drupal\courier\ChannelInterface|null
   *   A message, or NULL.
   */
  public function getTemplate($channel_type_id);

  /**
   * Get all templates associated with this collections.
   *
   * @return \Drupal\courier\ChannelInterface[]
   *   An array of template entities.
   */
  public function getTemplates();

  /**
   * Sets a template for this collection.
   *
   * Collections can accept one of each channel entity type.
   *
   * @param \Drupal\courier\ChannelInterface $template
   *   A template entity.
   *
   * @return \Drupal\courier\TemplateCollectionInterface
   *   Return this object for chaining.
   */
  public function setTemplate(ChannelInterface $template);

  /**
   * Removes a template with the channel entity type.
   *
   * @param string $channel_type_id
   *   A channel entity type ID.
   *
   * @return \Drupal\courier\TemplateCollectionInterface
   *   Return this object for chaining.
   */
  public function removeTemplate($channel_type_id);

  /**
   * Ensures tokens specified by context have values in this collection.
   *
   * @throws \Exception
   *   Throws exception if there are missing values.
   */
  public function validateTokenValues();

  /**
   * Locates the template collection which references a template.
   *
   * @param \Drupal\courier\ChannelInterface $template
   *   A template entity.
   *
   * @return \Drupal\courier\Entity\TemplateCollection|null
   *   A template collection entity, or NULL if the template is an orphan.
   */
  public static function getTemplateCollectionForTemplate(ChannelInterface $template);

}
