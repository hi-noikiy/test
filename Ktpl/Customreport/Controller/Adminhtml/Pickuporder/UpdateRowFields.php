<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Pickuporder;
class UpdateRowFields extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        return parent::__construct($context);
    }
    
    public function execute()
    {
        
        $fieldId = (int) $this->getRequest()->getParam('id');
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if($data = $this->getRequest()->getPost()) { 
            if($data['wholesaleprice'] == "") { 
                $data['wholesaleprice'] == NULL; 
            } 
          
            if($data['deposit'] == "") { 
                $data['deposit'] == NULL; 
            }
           
            if ($fieldId) {
                $model = $objectManager->create('Ktpl\Customreport\Model\Pickuporder');
                $model->setData((array)$data)->setId($fieldId);
                $model->save();
                 
                $upmodel = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->load($fieldId);

                if($data['status'] == '3' && $upmodel->getDelivery() == NULL) {
                    
                    $deliverymodel = $objectManager->create('Ktpl\Customreport\Model\Deliveryorder')->load($upmodel->getPickupId(), 'pickupid');
                    $deliverymodel->setOrderId($upmodel->getOrderId());
                    $deliverymodel->setRealOrderId($upmodel->getRealOrderId());
                    $deliverymodel->setPickupid($upmodel->getPickupId());
                    $deliverymodel->setCustomerName($upmodel->getCustomerName());
                    $deliverymodel->setTelephone($upmodel->getTelephone());
                    $deliverymodel->setAddress($upmodel->getAddress());
                    $deliverymodel->setProductName($upmodel->getProductName());
                    $deliverymodel->setSku($upmodel->getSku());
                    $deliverymodel->setQty($upmodel->getQty());
                    $deliverymodel->setAttributes($upmodel->getAttributes());
                    $deliverymodel->setPaymentMethod($upmodel->getPaymentMethod());
                    //$deliverymodel->setDeposit($upmodel->getDeposit());
                    $deliverymodel->setCustomerComment($upmodel->getDeliveryComment());
                    $deliverymodel->setStatus(1);
                    $deliverymodel->setOrderCreatedDate($upmodel->getOrderCreatedDate());
                    $deliverymodel->save();
                    echo '1';
                }
            }
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
        //return $this->_authorization->isAllowed('Ktpl_Customreport::pickup');
    }            
  	                 
}
