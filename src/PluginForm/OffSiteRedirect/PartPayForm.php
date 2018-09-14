<?php

namespace Drupal\commerce_partpay\PluginForm\OffSiteRedirect;

use Drupal\commerce_partpay\PartPay\PartPay;
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
  protected $partPay;

  /**
   * PartPayOffSiteForm constructor.
   */
  public function __construct(PartPay $partPay) {
    $this->partPay = $partPay;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    $this->partPay->init();

    $transaction = $this->partPay->prepareTransaction($payment, $form);

    $conf = $this->partPay->getConfiguration();

    $response = $this->partPay->createOrder($transaction);

    if ($response->getStatusCode() !== 200) {
      $this->partPay->logger->error('Error');
    }
//
//    $form = $this->buildRedirectForm(
//      $form,
//      $form_state,
//      $request->getRedirectUrl(),
//      [],
//      $request->getRedirectMethod());
//    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_partpay.partpay')
    );
  }

}
