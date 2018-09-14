<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentInterface;
use GuzzleHttp\RequestOptions;

/**
 * Class Payment Express Service.
 *
 * @package Drupal\commerce_partpay
 */
class PartPay extends AbstractAbstractPartPayRequest {

  public function init() {

    if(!$this->hasToken()) {
      $this->setClientId($this->getSettings('partpayClientId'));
      $this->setSecret($this->getSettings('partpaySecret'));

      $response = $this->createToken();

      if (is_object($response) || property_exists($response, 'access_token') || property_exists($response, 'expires_in')) {
        $this->saveToken($response->access_token, $response->expires_in);
        $this->setTokenRequestMode(FALSE);
      }

    }

  }

  public function createToken() {

    $this->setTokenRequestMode();

    $options = [
      RequestOptions::JSON => [
        "client_id" => $this->getClientId(),
        "client_secret" => $this->getSecret(),
        "audience" => $this->getAudience(),
        "grant_type" => "client_credentials",
      ],
    ];

    return $this->request('POST', '/oauth/token', $options);
  }

  public function saveToken($token, $expiry) {
    \Drupal::state()->set('partPayToken', $token);
    \Drupal::state()->set('partPayTokenExpiry', time() + $expiry);
  }

  public function hasToken() {

    $token = \Drupal::state()->get('partPayToken');
    $expires = \Drupal::state()->get('partPayTokenExpiry');

    if (!empty($expires) && !is_numeric($expires)) {
      $expires = strtotime($expires);
    }

    $result = !empty($token) && time() < $expires;

    if ($result) {
      $this->setToken($token);
      $this->setTokenExpiry($expires);
    }

    return $result;

  }

  public function getConfiguration() {
    return $this->request('GET', '/configuration');
  }

  public function prepareTransaction(PaymentInterface $payment, $form) {

    $order = $payment->getOrder();

    /** @var \Drupal\commerce_order\Entity\OrderInterface $orderItems */
    $orderItems = $order->getItems();

    $items = [];

    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $item */
    foreach ($orderItems as $item) {

      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $purchasableEntity */
      $purchasableEntity = $item->getPurchasedEntity();

      $items[] = [
        "description" => sprintf("%s: %s", ucwords($purchasableEntity->bundle()), $item->getTitle()),
        "name" => $item->getTitle(),
        "sku" => $purchasableEntity->getSku(),
        "quantity" => intval($item->getQuantity()),
        "price" => number_format($item->getTotalPrice()->getNumber(), 2),
      ];
    }


    $billingProfile = $order->getBillingProfile();

    /** @var \Drupal\address\AddressInterface $billingAddress */
    $billingAddress = $billingProfile->get('address')->first();


    $data = [
      'amount' => number_format($payment->getAmount()->getNumber(), 2),
      'consumer' => [
        'givenNames' => $billingAddress->getGivenName(),
        'surname' => $billingAddress->getFamilyName(),
        'email' => $order->getEmail(),
      ],
      'billing' => [
        'addressLine1' => $billingAddress->getAddressLine1(),
        'addressLine2' => $billingAddress->getAddressLine2(),
        'suburb' => $billingAddress->getDependentLocality(),
        'city' => $billingAddress->getLocality(),
        'postcode' => $billingAddress->getPostalCode(),
        'state' => $billingAddress->getAdministrativeArea(),
      ],
//      'shipping' => [
//        'addressLine1' => '23 Duncan Tce',
//        'addressLine2' => ',
//        'suburb' => 'Kilbirnie',
//        'city' => 'Wellilngton',
//        'postcode' => '1000',
//        'state' => '
//      ],
      'description' => sprintf('%s #%d', $this->getReference(), $order->id()),
      'items' => $items,
      'merchant' => [
        'redirectConfirmUrl' => $form['#return_url'],
        'redirectCancelUrl' => $form['#cancel_url'],
      ],
      'merchantReference' => sprintf('%s #%d', $this->getReference(), $order->id()),
//      'taxAmount' => 0,
//      'shippingAmount' => 5,
    ];

    return $data;
  }

  public function createOrder(array $transaction) {

    $this->init();

    $options = [
      RequestOptions::JSON => $transaction,
    ];

    return $this->request('POST', '/order', $options);
  }

}
