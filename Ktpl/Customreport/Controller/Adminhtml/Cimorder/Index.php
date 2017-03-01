<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;
class Index extends \Magento\Backend\App\Action
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
         $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ktpl_Customreport::a_menu_item1');
        $resultPage->addBreadcrumb(__('Customreport'), __('Customreport'));
        $resultPage->addBreadcrumb(__('Manage Cimorder'), __('Manage Cimorder'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cim Credit Orders'));
        return $resultPage;
    }    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
    }            
        
}
