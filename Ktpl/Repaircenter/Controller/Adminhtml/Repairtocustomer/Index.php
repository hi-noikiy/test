<?php
namespace Ktpl\Repaircenter\Controller\Adminhtml\Repairtocustomer;
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
        $resultPage->setActiveMenu('Ktpl_Repaircenter::a_menu_item7');
        $resultPage->addBreadcrumb(__('Repaircenter'), __('Repaircenter'));
        $resultPage->addBreadcrumb(__('Repair to customer'), __('Repair to customer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Repair to customer'));
        return $resultPage;
    }    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
    }            
        
}
