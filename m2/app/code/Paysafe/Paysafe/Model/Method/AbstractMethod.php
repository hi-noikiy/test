<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysafe\Paysafe\Model\Method;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var string
     */
    protected $_code= 'paysafe_abstract';

    /**
     * @var string
     */
    public $logo = '';

    /**
     * @var string
     */
    protected $_methodTitle = '';

    /**
     * @var string
     */
    protected $brand = '';

    const ACCEPT_STATUS = 'payment_accepted';
    const PS_STATUS = 'payment_ps';
    const IR_STATUS = 'payment_inreview';
    const SP_STATUS = 'payment_sp';
    const RP_STATUS = 'payment_rp';

    /**
     *
     * @param  string $paymentAction
     * @param  object $stateObject
     * @return object
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case self::ACTION_ORDER:
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus($this->getConfigData('order_status'));
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * is payment method available or not
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return boolean
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isAvailable = parent::isAvailable($quote);

        if ($isAvailable) {
            $isActive = $this->getConfigData('active');
            if (!$isActive) {
                return false;
            }
        }
        return $isAvailable;
    }

    /**
     * get payment title
     *
     * @return string
     */
    public function getTitle()
    {
        return __($this->_methodTitle);
    }

    /**
     * get a logo
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * get environment
     * @param string $config
     * @return string
     */
    public function getSpecificConfiguration($config)
    {
        return $this->getConfigData($config);
    }

    /**
     * get a brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }
    
    /**
     * capture a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  int $amount
     * @return object
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperCore = $objectManager->create('Paysafe\Paysafe\Helper\Core');

        $orderId = $payment->getData()['entity_id'];
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $transactionId = $payment->getAdditionalInformation('TRANSACTION_ID');

        if (!$payment->getAdditionalInformation('SETTLEMENT_STATUS')) {
            $credentials =  $helperCore->getGeneralCredentials();
            $credentials['environment'] = $this->getSpecificConfiguration('environment');
            $captureParameters['merchantRefNum'] = $payment->getAdditionalInformation('MERCHANT_REFNUM');
            $captureResult = $helperCore->captureProcess($transactionId, $credentials, $captureParameters);

            if ($captureResult && !isset($captureResult['error']['message']) && $captureResult['status'] != 'FAILED') {
                if ($captureResult['status'] == 'COMPLETED') {
                    $payment->setStatus('APPROVED')
                        ->setAdditionalInformation('SETTLEMENT_ID', $captureResult['id'])
                        ->setAdditionalInformation('SETTLEMENT_STATUS', $captureResult['status'])
                        ->setTransactionId($captureResult['id'])
                        ->setIsTransactionClosed(1)->save();
                } elseif ($captureResult['status'] == 'PENDING') {
                    $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::SP_STATUS;
                    $order->setState('new')->setStatus($orderStatus)->save();
                    $order->addStatusToHistory($orderStatus, '', true)->save();
                    $payment->setTransactionId($captureResult['id'])->save();
                    $payment->setAdditionalInformation('SETTLEMENT_ID', $captureResult['id'])->save();
                    $payment->setAdditionalInformation('SETTLEMENT_STATUS', $captureResult['status'])->save();

                    throw new \Magento\Framework\Exception\LocalizedException(__('You can not capture at this moment because the settlement status was pending. Please try again when the settlement status is not pending'));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('An error occurred while processing'. ' : '. $captureResult['error']['message']));
            }
        } else {
            $payment->setStatus('APPROVED')
                    ->setTransactionId($payment->getAdditionalInformation('TRANSACTION_ID'))
                    ->setIsTransactionClosed(1)->save();
        }
        return $this;
    }

    /**
     * refund a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  int $amount
     * @return object
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperCore = $objectManager->create('Paysafe\Paysafe\Helper\Core');

        $settlementId = $payment->getAdditionalInformation('SETTLEMENT_ID');
        $refundCredentials = $helperCore->getGeneralCredentials();
        $refundCredentials['environment'] = $this->getSpecificConfiguration('environment');
        $refundParameters['merchantRefNum'] = $payment->getAdditionalInformation('MERCHANT_REFNUM');
        $refundParameters['amount'] = str_replace('.', '', number_format((float)$amount, 2, '.', ''));
        $refundResult = $helperCore->refundProcess($settlementId, $refundCredentials, $refundParameters);

        if(isset($refundResult['status'])){
            if ($refundResult['status'] == 'COMPLETED') {
                $payment->setStatus('APPROVED')
                        ->setTransactionId($refundResult['id'])
                        ->setAdditionalInformation('REFUND_ID', $refundResult['id'])
                        ->setAdditionalInformation('REFUND_STATUS', $refundResult['status'])
                        ->setIsTransactionClosed(1)->save();
            } elseif ($refundResult['status'] == 'PENDING') {
                $payment->setTransactionId($refundResult['id'])->save();
                $payment->setAdditionalInformation('REFUND_ID', $refundResult['id'])->save();
                $payment->setAdditionalInformation('REFUND_STATUS', $refundResult['status'])->save();
            } elseif ($refundResult['status'] == 'FAILED') {
                throw new \Magento\Framework\Exception\LocalizedException(__('An error occurred while processing'));
            }
        } elseif (!$refundResult || isset($refundResult['error']['message'])) {
            $errorMessage = 'Refund failed';
            if (isset($refundResult['error']['message'])) {
                $errorMessage = $refundResult['error']['message'];
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
        }
        return $this;
    }

}
