<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PartPayService.
 *
 * @package Drupal\commerce_partpay
 */
class PartPayService extends PartPay implements PartPayServiceInterface {

  /**
   * Prepare xml request data to PartPay.
   */
  public function preparePartPayTransaction(array $form, PaymentInterface $payment) {

    $this->gateway->setCurrency($payment->getAmount()->getCurrencyCode());

    $this->gateway->setParameter('returnUrl', $form['#return_url']);

    $this->gateway->setParameter('cancelUrl', $form['#cancel_url']);

    $this->gateway->setParameter('amount', $payment->getAmount()->getNumber());

    $this->gateway->setParameter('description', $this->getReference() . ' #' . $payment->getOrderId());

  }

}
