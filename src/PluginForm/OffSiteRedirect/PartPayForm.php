<?php

namespace Drupal\commerce_partpay\PluginForm\OffSiteRedirect;

use Drupal\commerce_partpay\PartPay\PartPayServiceInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PartPayOffSiteForm.
 *
 * @package Drupal\commerce_partpay\PluginForm\OffsiteRedirect
 */
class PartPayForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  /**
   * The PartPay Service.
   */
  protected $partPayService;

  /**
   * Partpay gateway.
   */
  protected $gateway;

  /**
   * PartPayOffSiteForm constructor.
   */
  public function __construct(PartPayServiceInterface $partPayService) {
    $this->partPayService = $partPayService;
    $this->gateway = $partPayService->getGateway();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    $this->partPayService->preparePartPayTransaction($form, $payment);

    if ($this->partPayService->getConfiguration('mode') === 'test') {
      $this->gateway->setTestMode(TRUE);
    }

    /** @var \Omnipay\PaymentExpress\Message\PxPayAuthorizeResponse $request */
    $request = $this->gateway->purchase()->send();

    if (empty($request->getRedirectUrl())) {
      $this->partPayService->logger->error($request->getData()->ResponseText);
    }

    if (!$this->partPayService->isValidateCurrency($payment->getAmount()->getCurrencyCode())) {
      throw new PaymentGatewayException('Invalid currency. (' . $payment->getAmount()->getCurrencyCode() . ')');
    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_partpay.partpay_service')
    );
  }

}
