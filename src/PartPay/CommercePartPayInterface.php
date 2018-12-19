<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Dps Interface.
 */
interface CommercePartPayInterface {

  /**
   * Capture the payment.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order entity.
   * @param \stdClass $response
   *   Omnipay response.
   */
  public function capturePayment(OrderInterface $order, \stdClass $response);

}
