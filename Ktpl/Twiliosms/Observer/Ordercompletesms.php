<?php
namespace Ktpl\Twiliosms\Observer;
use Twilio\Rest\Client; 
use Magento\Framework\Event\ObserverInterface;
 
class Ordercompletesms implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig    
    ) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }
 
    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->scopeConfig->getValue('twiliosms/general/status', $storeScope);
        $order = $observer->getEvent()->getOrder();
        if($order->getState() == 'complete' && $enable) {
            $orderid = $order->getIncrementId();
            $name = $order->getCustomerName();
            $telephone = $order->getBillingAddress()->getTelephone();
            $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

            try {
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $AccountSid = $this->scopeConfig->getValue('twiliosms/general/account_id', $storeScope);
                $AuthToken = $this->scopeConfig->getValue('twiliosms/general/auth_token', $storeScope);
                $msg = $this->scopeConfig->getValue('twiliosms/general/order_complete_sms', $storeScope);
                $from = $this->scopeConfig->getValue('twiliosms/general/from_no', $storeScope);

                $client = new Client($AccountSid, $AuthToken);

                $msg = str_replace('{{name}}', $name, $msg);
                $msg = str_replace('{{orderid}}', $orderid, $msg);
    //            $ini = substr($telephone,0,3);
    //            if($ini =='230'){$telephone = '+'.$telephone; }
    //            else if($ini != '+23'){$telephone = '+230'.$telephone;}
//                $message = $client->account->messages->create(array(
//                    "From" => $from, 
//                    "To" =>  $telephone,
//                    "Body" => $msg,
//                ));
                $message = $client->messages->create(
                    $telephone,
                    array(
                        'from' => $from,
                        'body' => $msg,
                    )
                );
            } catch (\Exception $e) {
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mysms.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info("No:" . $telephone . ':' ."Order No:" . $orderid . ':'.'Error :'.$e->getMessage());
                #echo 'Message: ' .$e->getMessage();
            }
        }    
            
    }
}