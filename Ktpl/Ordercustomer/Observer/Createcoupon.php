<?php
namespace Ktpl\Ordercustomer\Observer;

class Createcoupon implements \Magento\Framework\Event\ObserverInterface
{
    protected $_objectManager;
    protected $request;
    protected $authSession;
 
    public function __construct(
        \Magento\Framework\App\Request\Http $request,   
        \Magento\Backend\Model\Auth\Session $authSession,       
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->authSession = $authSession;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $data = array();
            $datapickup = array();
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder(); 
            $user = $this->authSession->getUser();
            $username = $user->getUsername();
            $payment_info = ($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle();

            $data['increment_id'] = $order->getIncrementId();
            //$datapickup['real_order_id'] = $order->getId(); 
            $data['username']     = $username;
            $data['payment_type'] = $payment_info;
            //$datapickup['payment_method'] = $payment_info;
            $data['invoice_comment'] = "";
            $data['order_created_date'] = $order->getCreatedAt();
            //$datapickup['order_created_date'] = date('Y-m-d H:i:s');
            $data['customer_name'] =  $order->getCustomerFirstname().' '.$order->getCustomerLastname();
            $datapickup['telephone'] = $order->getBillingAddress()->getData('telephone');
            $datapickup['address'] = $order->getBillingAddress()->getData('street');
            $datapickup['delivery_comment'] = $order->getCustomerNote();

            $items = $order->getAllItems();
            foreach($items as $item) {
                if($item->getData('product_options')) {
                    $pots= $item->getProductOptions();
                    if(isset($pots['options'])){
                        $custom_option = $pots['options'][0]['value']; 	
                    }
                    else{$custom_option = ""; }
                } else {
                        $custom_option = "";
                }

                $data['product_name'] =  $item->getData('name');
                $data['product_subtitle'] = $item->getProduct()->getNewSku();
                $data['customtitle'] =  $custom_option;
                $data['product_sku'] =  $item->getSku();
                $data['price'] =  $item->getPrice();

                $model = $this->_objectManager->create('Ktpl\Ordercustomer\Model\Ordercustomer');
                $model->setData($data);
                $model->setCreatedTime(date('Y-m-d H:i:s'));
                $model->save();
            } 
        } catch(\Exception $e){
                echo $e->getMessage(); 
        }
        
    }
}
