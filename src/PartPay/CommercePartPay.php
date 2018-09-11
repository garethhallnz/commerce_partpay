<?php

namespace Drupal\commerce_partpay\PartPay;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides Base DPS class.
 */
abstract class CommercePartPay extends OffsitePaymentGatewayBase implements CommercePartPayInterface {

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful()) {
      $order = $request->attributes->all()['commerce_order'];
      $this->capturePayment($order, $response);
      $order->state = 'completed';
      $order->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && !$response->isSuccessful()) {
      $this->capturePayment($order, $response);
    }

    $response_data = $response->getData();

    if (!$response->isSuccessful() && !empty($response_data->ReCo) && $response_data->ReCo[0] != 'RC') {

      $message = ucwords(strtolower($response->getMessage()));

      drupal_set_message(
        $this->t(
          'Sorry @gateway failed with "@message". You may resume the checkout process on this page when you are ready.',
          [
            '@message' => $message,
            '@gateway' => $this->getDisplayLabel(),
          ]
        ),
        'error'
      );
    }
    else {
      parent::onCancel($order, $request);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful() && $order->state->value !== 'completed') {
      $this->capturePayment($order, $response);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(OrderInterface $order, ResponseInterface $response) {

    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

    $data = [
      'state' => ucfirst(strtolower($response->getMessage())),
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'remote_id' => $response->getTransactionId(),
      'remote_state' => $response->getMessage(),
      'authorized' => \Drupal::time()->getRequestTime(),
    ];

    /** @var \Drupal\commerce_partpay\PartPay\PartPay $partPayService */
    $partPayService = $this->PartPayService;

    $module_handler = $partPayService->getModuleHandler();

    $module_handler->alter('commerce_partpay_capture_payment', $data, $order, $response);

    $payment = $payment_storage->create($data);

    $payment->save();
  }

}
