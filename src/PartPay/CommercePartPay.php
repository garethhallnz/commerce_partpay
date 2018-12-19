<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides Base DPS class.
 */
abstract class CommercePartPay extends OffsitePaymentGatewayBase implements CommercePartPayInterface {

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {

    /* @var \Drupal\commerce_partpay\PartPay\PartPay $partPay */
    $partPay = $this->partPay;

    $response = $partPay->getOrder($request->get('orderId') . 'd');

    if (!$response) {

      $message = $this->t(
        'Sorry @gateway failed with "@message". You may resume the checkout process on this page when you are ready.',
        [
          '@message' => ucwords(strtolower($response->getReasonPhrase())),
          '@gateway' => $this->getDisplayLabel(),
        ]
      );

      \Drupal::messenger()->addMessage($message, 'error');
    }
    else {
      parent::onCancel($order, $request);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {

    /* @var \Drupal\commerce_partpay\PartPay\PartPay $partPay */
    $partPay = $this->partPay;

    $response = $partPay->getOrder($request->get('orderId'));

    if ($partPay->isSuccessful($response) && $order->state->value !== 'completed') {
      $this->capturePayment($order, $response);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(OrderInterface $order, \stdClass $response) {

    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

    $requestTime = \Drupal::time()->getRequestTime();

    $data = [
      'state' => 'fulfillment',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'remote_id' => $response->orderId,
      'remote_state' => $response->orderStatus,
      'authorized' => $requestTime,
      'completed' => $requestTime,
    ];

    $payment = $payment_storage->create($data);

    $payment->save();
  }

}
