<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Pickuporder;
class UpdateMarkupField extends \Magento\Backend\App\Action
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
        $wholesaleprice = (int) $this->getRequest()->getParam('wholesalerprice');

        if($wholesaleprice > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $pickupmodel = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->load($fieldId);
           // $pickupmodel = Mage::getModel('customreport/salespickuporder')->load($fieldId);
            $retail_price = $pickupmodel->getRetailPrice();
            $markup = (($retail_price - $wholesaleprice) * 100) / $retail_price;
            $markup = round($markup);
            $pickupmodel->setWholesalePrice($wholesaleprice);
            $pickupmodel->setMarkup($markup);
            $pickupmodel->save();
            echo $markup;
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
        //return $this->_authorization->isAllowed('Ktpl_Customreport::pickup');
    }            
  	                 
}
