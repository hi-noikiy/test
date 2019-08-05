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

namespace Paysafe\Paysafe\Controller\Payment;

class Response extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * set/get magento session
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected function getSessionObject()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('\Magento\Catalog\Model\Session');
    }
    /**
     * get payment responses
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('orderId');
        if (!isset($orderId)) {
            $orderId = $this->getSessionObject()->getOrderId();
        }
        $this->_order = $this->getOrderByIncerementId($orderId);
        $recurring = $this->getRequest()->getParam('recurring');
        $this->getSessionObject()->setRecurring($recurring);

        if (!isset($this->_order)) {
            $this->_logger->info('Order is not found');
            $this->_logger->info('Error Before Redirect');
            $this->redirectError('Error Before Redirect');
            return false;
        }
        $paymentMethod = $this->_order->getPayment()->getMethod();
        $this->method = $this->_order->getPayment()->getMethodInstance();
        $credentials = $this->helperCore->getGeneralCredentials();
        $credentials['environment'] = $this->method->getSpecificConfiguration('environment');

        if (array_key_exists('PaRes', $this->getRequest()->getPostValue())) {
            $paymentParameters = $this->getSessionObject()->getPaymentParameters();
            $authenticationParameters['merchantRefNum'] = $paymentParameters['merchantRefNum'];
            $authenticationParameters['paRes'] = $this->getRequest()->getPostValue()['PaRes'];
            $credentials['enrollmentId'] = $this->getSessionObject()->getEnrollmentId();
            $threedsecureAuthenticationResponse = $this->helperCore->validate3dsAuthentication($credentials, $authenticationParameters);
            
            if(isset($threedsecureAuthenticationResponse['eci'])) {
                $paymentParameters['authentication']['eci'] = $threedsecureAuthenticationResponse['eci'];
                $paymentParameters['authentication']['cavv'] = $threedsecureAuthenticationResponse['cavv'];
                $paymentParameters['authentication']['xid'] = $threedsecureAuthenticationResponse['xid'];
                $paymentParameters['authentication']['threeDEnrollment'] = $this->getSessionObject()->getThreeDEnrollment();
                $paymentParameters['authentication']['threeDResult'] = $threedsecureAuthenticationResponse['threeDResult'];
                $paymentParameters['authentication']['signatureStatus'] = $threedsecureAuthenticationResponse['signatureStatus'];
                $this->processPayment($credentials, $paymentParameters);
            } else {
                $this->redirectError('An error occurred while processing');
            }
        } else {
            $this->_logger->info('process payment response');

            if ($recurring == 'RECURRING') {
                $paymentToken = $this->getRequest()->getParam('paysafe_token_recurring');
            } else {
                $paymentToken = $this->getRequest()->getParam('paysafe_token');
            }

            $this->getSessionObject()->setPaymentToken($paymentToken);

            if (!isset($paymentToken)) {
                $this->_logger->info('payment token is not found');
                $this->_logger->info('Error Before Redirect');
                $this->redirectError('Error Before Redirect');
                return false;
            }
            
            $paymentParameters = $this->getPaymentParameters($paymentToken);

            if((bool)$this->method->getSpecificConfiguration('threedsecure')) {
                $enrollmentParameters['merchantRefNum'] = $paymentParameters['merchantRefNum'];
                $enrollmentParameters['amount'] = $this->getRequest()->getParam('amount');
                $enrollmentParameters['currency'] = $this->getRequest()->getParam('currency_code');
                $enrollmentParameters['customerIp'] = $paymentParameters['customerIp'];
                $enrollmentParameters['merchantUrl'] = $credentials['merchant_url'];
                $enrollmentParameters['card']['paymentToken'] = $paymentParameters['card']['paymentToken'];
                $threedsecureResponse = $this->helperCore->enrollmentLookup($credentials, $enrollmentParameters);
                if(!isset($threedsecureResponse['paReq']) && !isset($threedsecureResponse['acsURL'])) {
                    $this->_logger->info('paReq is not found');
                    $this->_logger->info('Credit Card entered is not 3D secure enabled');
                    $this->redirectError('Credit Card entered is not 3D secure enabled, please use a different card.');
                }
                $this->getSessionObject()->setPaymentParameters($paymentParameters);
                $this->getSessionObject()->setEnrollmentId($threedsecureResponse['id']);
                $this->getSessionObject()->setThreeDEnrollment($threedsecureResponse['threeDEnrollment']);
                $this->getSessionObject()->setOrderId($orderId);
                $url = 'paysafe/payment/pares';
                $this->_redirect(
                    $url,
                    ['paReq' => base64_encode($threedsecureResponse['paReq']), 'acsURL' => base64_encode($threedsecureResponse['acsURL'])]
                );
            } else {
                $this->processPayment($credentials, $paymentParameters);
            }
        }
    }

    /**
     * clear session
     * @return void
     */
    public function clearSession() {
        $this->getSessionObject()->unsPaymentParameters();
        $this->getSessionObject()->unsEnrollmentId();
        $this->getSessionObject()->unsThreeDEnrollment();
        $this->getSessionObject()->unsOrderId();
    }

    /**
     * process a payment
     * @param array $credentials
     * @param array $paymentParameters
     * @return void
     */
    protected function processPayment($credentials, $paymentParameters) {
        $response = $this->helperCore->doPayment($credentials, $paymentParameters);
        $this->saveOrderAdditionalInformation($response);
        $this->clearSession();

        if (!isset($response['error']['code'])) {
            switch ($response['status']) {
                case 'COMPLETED':
                case 'RECEIVED':
                case 'HELD':
                    $this->processSuccessPayment($response);
                    break;
                case 'FAILED':
                    $this->_logger->info('process payment response : failed payment');
                    $this->redirectError('Payment Failed');
                    break;
                case 'CANCELLED':
                    $this->_logger->info('process payment response : cancelled payment');
                    $this->redirectError('Payment Cancelled');
                    break;
            }
        } else {
            $this->_logger->info('process payment response : failed payment');
            $this->_logger->info('failed payment: '.$response['error']['message']);
            $this->redirectError($response['error']['message']);
        }
    }

    /**
     * process a success payment
     * @param array $response
     * @return void
     */
    protected function processSuccessPayment($response)
    {
        $this->_logger->info('process payment response : success payment');
        
        $orderStatus = $this->setOrderStatus($response);
        $this->_logger->info('current oders status '. $orderStatus);

        if ($orderStatus) {
            $this->_order->setState('new')->setStatus($orderStatus)->save();
            $this->_order->addStatusToHistory($orderStatus, '', true)->save();
        } else {
            $this->createInvoice($this->_order);
        }
        $orderSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
        $orderSender->send($this->_order);

        $this->deactiveQuote();
        $this->_checkoutSession->setLastRealOrderId($this->_order->getIncrementId());

        $isRecurringActive = $this->helperCore->getGeneralCredentials()['recurring'];

        if($isRecurringActive && $this->getSessionObject()->getRecurring() == 'INITIAL' && $this->customer->isLoggedIn()) {
            $paymentToken = $this->getSessionObject()->getPaymentToken();
            $this->getSessionObject()->unsRecurring();
            $this->getSessionObject()->unsPaymentToken();
            $this->_redirect($this->_url->getUrl('paysafe/payment/recurringresponse',
                [
                    'payment_method' => $this->_order->getPayment()->getMethod(),
                    'action' => 'register_on_checkout',
                    'paysafe_token' => $paymentToken,
                    'order' => true,
                    '_secure' => true
                ]
            ));
        } else {
            $this->_redirect('checkout/onepage/success', ['_secure' => true]);
        }

    }

    /**
     * set an order status
     * @param array $response
     */
    protected function setOrderStatus($response)
    {
        if($response['status'] == 'RECEIVED' ||
            $response['status'] == 'HELD') {
            $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::IR_STATUS;
        } elseif ($response['status'] == 'COMPLETED') {
            if (!$response['settleWithAuth']) {
                $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::PS_STATUS;
            } else {   
                if ($response['settlements'][0]['status'] == 'PENDING') {
                    $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::SP_STATUS;
                } else {
                    $orderStatus = false;
                }
            }
        }
        return $orderStatus;
    }
}
