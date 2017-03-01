<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Pickuporder;
class UpdatePickupaddress extends \Magento\Backend\App\Action
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
        $wholesalerid = $this->getRequest()->getParam('wholesalerid');
        $fieldId = (int) $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $whole = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->load($wholesalerid);
        $pickupmodel = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->load($fieldId);
        $pickupmodel->setWholesalerId($wholesalerid);    
        $pickupmodel->setPickupAddress($whole->getAddress());
        $pickupmodel->save();

        echo $whole->getAddress();
              
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
        //return $this->_authorization->isAllowed('Ktpl_Customreport::pickup');
    }            
  	                 
}
