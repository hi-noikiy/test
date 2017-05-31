<?php
namespace Ktpl\Repaircenter\Controller\Adminhtml\Repairtocenter;
class Create extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $request;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,     
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        $this->request = $request;        
        return parent::__construct($context);
    }
    
    public function execute()
    {
        try{
            $result = array();
            $fieldId = (int) $this->request->getParam('order_id');
            $sku= base64_decode($this->request->getParam('sku')); 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order =  $objectManager->create('\Magento\Sales\Model\Order')->load($fieldId);
            $repairdata = array();
            $repairdata['created_time'] = date('Y-m-d H:i:s');
            $repairdata['increment_id'] = $order->getIncrementId();
            $repairdata['pickup_address'] = $order->getBillingAddress()->getData('street');
            $repairdata['customer'] = $order->getBillingAddress()->getName().'<br /> T:'.
                    $order->getBillingAddress()->getData('telephone').'<br />E:'.$order->getCustomerEmail() ;
           
            $items = $order->getAllItems();
            foreach($items as $item) {
                if($item->getSku()==$sku){
                if($item->getData('product_options')) {
                   $opts = $item->getData('product_options');
                   $custom_option = $opts['options'][0]['value']; 	
                } else {
                   $custom_option = "";
                }
                if($custom_option){$custom_option.='<br />';}
                $repairdata['product']  = $item->getData('name').'<br />'.$item->getProduct()->getNewSku().
                       '<br />'.$custom_option.'SKU:'.$item->getSku();
                $pickup = $objectManager->create('\Ktpl\Customreport\Model\Pickuporder')->getCollection()
                           ->addFieldToFilter('sku',$item->getSku())
                           ->addFieldToFilter('order_id',$order->getIncrementId())
                           ->load();

                $repairdata['wholesaler'] = $pickup->getFirstItem()->getWholesaler_id(); 
                $repairdata['status'] = 1;

                   /* Insert data for repair order */
                   $repairmodel = $objectManager->create('\Ktpl\Repaircenter\Model\Repairtocenter');
                   $repairmodel->setData($repairdata);
                   $repairmodel->save(); 
            }
            }  
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['error_message'] = $e->getMessage();
        }
        if(isset($result['error'])) {
            $this->messageManager->addError($result['error_message']);
        } else {
            $this->messageManager->addSuccess(__('Repair Request created successfully.'));
        }
        $this->_redirect('sales/order/view',array('order_id'=>$fieldId));    
    }    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
    }            
        
}
