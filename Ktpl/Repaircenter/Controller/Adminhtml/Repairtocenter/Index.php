<?php
namespace Ktpl\Repaircenter\Controller\Adminhtml\Repairtocenter;
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
        $resultPage->setActiveMenu('Ktpl_Repaircenter::a_menu_item6');
        $resultPage->addBreadcrumb(__('Repaircenter'), __('Repaircenter'));
        $resultPage->addBreadcrumb(__('Repair to center'), __('Repair to center'));
        $resultPage->getConfig()->getTitle()->prepend(__('Repair to center'));
        return $resultPage;
    }    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
    }            
        
}
