<?php

namespace Drupal\commerce_partpay\PartPay;

/**
 * Provides a handler for IPN requests from PayPal.
 */
interface PartPayInterface {

  /**
   * Get PartPay gateway instance.
   */
  public function getGateway();

  /**
   * Set PartPay credentials.
   */
  public function setCredentials();

  /**
   * Set PartPay configuration property.
   */
  public function setConfiguration(array $configuration);

  /**
   * Set PartPay configuration property.
   */
  public function getConfiguration($key = NULL);

  /**
   * Get PartPay client id.
   */
  public function getClientId();

  /**
   * Get PartPay key.
   */
  public function getSecret();

  /**
   * Get merchant reference.
   */
  public function getReference();

  /**
   * Is this a valid currency.
   */
  public function isValidateCurrency($code);

  /**
   * Get the module handler.
   */
  public function getModuleHandler();

}
