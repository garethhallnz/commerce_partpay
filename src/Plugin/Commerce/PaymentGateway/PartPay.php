<?php

namespace Drupal\commerce_partpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_partpay\PartPay\CommercePartPay;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the PartPay payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "partpay",
 *   label = @Translation("PartPay"),
 *   display_label = @Translation("PartPay"),
 *   payment_method_types = {"credit_card"},
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_partpay\PluginForm\OffSiteRedirect\PartPayForm",
 *   },
 *   credit_card_types = {
 *     "amex", "discover", "mastercard", "visa",
 *   },
 * )
 */
class PartPay extends CommercePartPay {

  /**
   * PartPay Service.
   *
   * @var \Drupal\commerce_partpay\PartPay\PartPayServiceInterface
   */
  protected $partPayService;

  /**
   * PartPay gateway.
   */
  protected $gateway;

  /**
   * Constructs a new PaymentGatewayBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   The payment type manager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   The payment method type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager,
    TimeInterface $time
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $payment_type_manager,
      $payment_method_type_manager,
      $time
    );

    $this->partPayService = \Drupal::service('commerce_partpay.partpay_service');

    $this->gateway = $this->partPayService->getGateway();

    $this->partPayService->setConfiguration($configuration);

    $this->partPayService->setCredentials();

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge([
      'partpayClientId' => '',
      'partpaySecret' => '',
      'partpayRef' => 'Website Order',
    ], parent::defaultConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $key = 'partpayClientId';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Id'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'partpaySecret';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'partpayRef';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant Reference Prefix'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      foreach ($values as $key => $value) {
        if (preg_match("/^partpay(.*)$/i", $key)) {
          $this->configuration[$key] = $value;
        }
      }

    }
  }

}
