<?php

namespace Drupal\commerce_partpay\PartPay;

/**
 * Provides a handler for IPN requests from PayPal.
 */
interface AbstractPartPayInterface {

  /**
   * Set PartPay configuration property.
   */
  public function setSettings(array $configuration);

  /**
   * Set PartPay configuration property.
   */
  public function getSettings($key = NULL);

  /**
   * Get PartPay clientId.
   */
  public function getClientId();

  /**
   * Set PartPay clientId.
   */
  public function setClientId($clientId);

  /**
   * Set PartPay secret.
   */
  public function setSecret($secret);

  /**
   * Get PartPay secret.
   */
  public function getSecret();

  /**
   * Get auth token.
   */
  public function getToken();

  /**
   * Set auth token expiry.
   */
  public function setTokenExpiry($expiry);

  /**
   * Get auth token expiry.
   */
  public function getTokenExpiry();

  /**
   * Set auth token.
   */
  public function setToken($token);

  /**
   * Set Test Mode.
   */
  public function setTestMode();

  /**
   * Is Test Mode.
   */
  public function isTestMode();

  /**
   * Set Token Mode.
   */
  public function setTokenRequestMode($mode);

  /**
   * Set Token Mode.
   */
  public function isTokenRequestMode();

  /**
   * Get API endpoint url.
   */
  public function getEndpoint();

  /**
   * Get Token endpoint url.
   */
  public function getTokenEndpoint();

  /**
   * Get Audience url.
   */
  public function getAudience();

  /**
   * Get merchant reference.
   */
  public function getReference();

  /**
   * Get the module handler.
   */
  public function getModuleHandler();

  /**
   * Http request.
   */
  public function request($method, $resource, array $options);

}
