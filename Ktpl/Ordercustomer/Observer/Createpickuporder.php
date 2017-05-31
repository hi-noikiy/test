<?php
namespace Ktpl\Ordercustomer\Observer;

class Createpickuporder implements \Magento\Framework\Event\ObserverInterface
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
            $order = $observer->getEvent()->getOrder();
            $data_coll =  $this->request->getParams();
            
            $user = $this->authSession->getUser();
            $username = $user->getUsername();
            $datapickup['order_id'] = $order->getIncrementId();
            $datapickup['real_order_id'] = $order->getId(); 
            $data['username']     = $username;
            $datapickup['payment_method'] = ($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle();
            $datapickup['order_created_date'] = date('Y-m-d H:i:s');
            $data['customer_name'] = $datapickup['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
            if(trim($datapickup['customer_name']) ==''){
                $data['customer_name'] = $datapickup['customer_name'] = $order->getBillingAddress()->getName();
            }    
            $datapickup['telephone'] = $order->getBillingAddress()->getData('telephone');
            $datapickup['address'] = $order->getBillingAddress()->getData('street');
            $datapickup['delivery_comment'] = $order->getCustomerNote();

            $items = $order->getAllItems();
            foreach($items as $item) {
               // print_r($item->getData()); exit;
                if($item->getData('product_options')) {
                    $pots= $item->getProductOptions();
                    if(isset($pots['options'])){
                        $custom_option = $pots['options'][0]['value']; 	
                    }
                    else{$custom_option = ""; }
                } else {
                    $custom_option = "";
                }

                $datapickup['product_name'] = $item->getData('name');
                $datapickup['attributes'] = $custom_option;
                $datapickup['sku'] = $item->getSku();
                $datapickup['retail_price'] = $item->getPrice();
                $datapickup['qty'] = round($item->getQtyOrdered());
                $datapickup['status'] = 1;
                
                 /* Insert data for pickup order */
                 $pickup = $this->_objectManager->create('Ktpl\Customreport\Model\Pickuporder')->getCollection()
                           ->addFieldToFilter('sku',$item->getSku())
                           ->addFieldToFilter('order_id',$order->getIncrementId())
                           ->getFirstItem();
                
                  if($pickup->getPickupId()){
                        $pickupmodel = $this->_objectManager->create('Ktpl\Customreport\Model\Pickuporder')->load($pickup->getPickupId());
                  } else {
                        $pickupmodel = $this->_objectManager->create('Ktpl\Customreport\Model\Pickuporder');
                        $pickupmodel->setData($datapickup);
                        $pickupmodel->save();
                  }    
            }
        } catch(\Exception $e){
            echo $e->getMessage(); 
        }
       
    }
}
