<?php

require_once( Mage::getBaseDir('lib') . '/payfortFort/init.php');

class Payfort_Pay_PaymentController extends Mage_Core_Controller_Front_Action
{
    public $integrationType;
    public $pfConfig;
    public $pfPayment;
    public $pfHelper;
    public $pfOrder;
    
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->pfConfig        = Payfort_Fort_Config::getInstance();
        $this->pfPayment       = Payfort_Fort_Payment::getInstance();
        $this->pfHelper        = Payfort_Fort_Helper::getInstance();
        $this->pfOrder         = new Payfort_Fort_Order();
        $this->integrationType = 'redirection';
        parent::__construct($request, $response, $invokeArgs);
    }
    public function indexAction()
    {
        return;
    }

    public function setOptionAction()
    {

        $payentMethod = Mage::getSingleton('checkout/session')->getData('payfort_option');
        if (isset($_GET['payfort_option'])) {
            Mage::getSingleton('checkout/session')->setData('payfort_option', $_GET['payfort_option']);
        }
    }

    // The redirect action is triggered when someone places an order
    public function redirectAction()
    {
        //Loading current layout
        $this->loadLayout();

        $orderId  = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        
        $_order = $this->pfOrder->getLoadedOrder();
        if (!$_order) {
            Mage_Core_Controller_Varien_Action::_redirect('checkout/cart', array('_secure' => true));
        }
        
        if ($_order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $_order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, (bool) Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $this->__('Payfort pending payment.')
            )->save();
        }
        
        $paymentMethod = $this->pfOrder->getPaymentMethod();
        
        if($paymentMethod == PAYFORT_FORT_PAYMENT_METHOD_CC) {
            $this->integrationType = $this->pfConfig->getCcIntegrationType();
        } elseif($paymentMethod == PAYFORT_FORT_PAYMENT_METHOD_INSTALLMENTS){
            $this->integrationType = $this->pfConfig->getInstallmentsIntegrationType();
        } 
        
        $gatewayParams = $this->pfPayment->getPaymentRequestParams($paymentMethod, $this->integrationType);
        //Creating a new block
        $template = '';
        if ($this->integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE) {
            $this->getLayout()->getBlock('head')->addLinkRel('stylesheet', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
            $this->getLayout()->getBlock('head')->addCss('css/payfort/merchant-page.css');
            $this->getLayout()->getBlock('head')->addJs('payfort/payfort_fort.js');
            $template = 'merchant-page.phtml';
        }
        /*elseif($this->integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE2) {
            $template = 'merchant-page2.phtml';
        }*/
        else {
            //$this->getLayout()->getBlock('root')->setTemplate('page/empty.phtml');                        
            //$template = 'redirect.phtml';           
// Retrieve order
            ?>
            <div class="center wrapper">
                <div id="logo" class="center"></div>
                <p class="center title"><?php echo $this->__('Redirecting to Payfort ...') ?></p>
                <form name="payfortpaymentform" id="payfortpaymentform" method="post" action="<?php echo $gatewayParams['url']; ?>">
                    <?php foreach ($gatewayParams['params'] as $k => $v): ?>
                        <input type="hidden" name="<?php echo $k ?>" value="<?php echo $v ?>">
                    <?php endforeach; ?>
                    <input type="submit" value="" id="submit2" name="submit2">
                </form>
            </div>
            <script type="text/javascript">
                (function () {
                    setTimeout(function () {
                        document.payfortpaymentform.submit();
                    }, 1000);
                })();
            </script>
            <style type="text/css">
                #payfortpaymentform {
                    display:none;
                }
                .center {
                    width: 50%;
                    margin: 0 auto;
                }
                #logo {
                    background:url(<?php echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/base/default/images/payfort/payfort_logo.png' ?>) no-repeat;
                    background-size: 70% 70%;
                    width: 323px;
                    height: auto;
                    margin-top:50px;

                }
                .title {
                    text-align: center;
                    font-size:15px;
                    margin-top:50px;
                }
            </style>
            <?php exit;
        }

        $block = $this->getLayout()->createBlock(
                            'Mage_Core_Block_Template', 'payfort_fort_block', array('template' => 'payfort/pay/'.$template)
                    )
                    ->setData('gatewayParams', $gatewayParams['params'])
                    ->setData('gatewayUrl', $gatewayParams['url']);
        
        $this->getLayout()->getBlock('content')->append($block);

        //Now showing it with rendering of layout
        $this->renderLayout();
    }

    public function responseAction()
    {
        $this->_handleResponse('offline');
    }

    public function responseOnlineAction()
    {
        $this->_handleResponse('online');
    }

    private function _handleResponse($response_mode = 'online', $integration_type = 'redirection')
    {
        $response_params = $this->getRequest()->getParams();
        $this->integrationType = $integration_type;
        $success = $this->pfPayment->handleFortResponse($response_params, $response_mode, $integration_type);
        if ($success) {
            $redirectUrl = 'checkout/onepage/success';
        }
        else {
            $redirectUrl = 'checkout/onepage/index/goto/review';
        }
        if ($this->integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE) {
            echo '<script>window.top.location.href = "' . Mage::getUrl($redirectUrl) . '"</script>';
            exit;
        }
        else {
            Mage_Core_Controller_Varien_Action::_redirect($redirectUrl, array('_secure' => true));
        }
    }

    public function merchantPageResponseAction()
    {
        $this->_handleResponse('online', PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE);
    }

    public function getMerchantPageDataAction()
    {
        $orderId  = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        
        $_order = $this->pfOrder->getLoadedOrder();
        if (!$_order) {
            Mage_Core_Controller_Varien_Action::_redirect('checkout/cart', array('_secure' => true));
        }
        
        if ($_order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $_order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, (bool) Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $this->__('Payfort pending payment.')
            )->save();
        }
        
        $gatewayParams = $this->pfPayment->getPaymentRequestParams(PAYFORT_FORT_PAYMENT_METHOD_CC, PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($gatewayParams));
    }

    public function merchantPageCancelAction()
    {
        $this->pfPayment->merchantPageCancel();
        Mage::app()->getResponse()->setRedirect(Mage::getModel('core/url')->getUrl('checkout/cart/index'))
                ->sendResponse();
        exit;
    }

}
