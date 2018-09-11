<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\commerce_payment\Entity\PaymentInterface;

/**
 * Provides a handler for from PartPay.
 */
interface PartPayServiceInterface {

  /**
   * Prepare xml request data to PartPay.
   */
  public function preparePartPayTransaction(array $form, PaymentInterface $payment);

}
