<?php

namespace Drupal\courier;

/**
 * Interface for TokenTrait.
 *
 * Token values and options are stored for the session, they are not stored.
 */
interface TokenInterface {

  /**
   * Gets token values.
   *
   * @return array
   *   Token values keyed by token type.
   */
  public function getTokenValues();

  /**
   * Sets a value to a token type.
   *
   * @param string $token
   *   A token type.
   * @param mixed $value
   *   The token value.
   *
   * @return self
   *   Return this instance for chaining.
   */
  public function setTokenValue($token, $value);

  /**
   * Gets token options as required by \Drupal::token()->replace().
   *
   * @return array
   *   An array of token options.
   */
  public function getTokenOptions();

  /**
   * Sets a token option.
   *
   * @param string $option
   *   The token option name.
   * @param mixed $value
   *   The token option value.
   *
   * @return self
   *   Return this instance for chaining.
   */
  public function setTokenOption($option, $value);

}
