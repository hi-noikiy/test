<?php
namespace Ktpl\Ordercustomer\Controller\Adminhtml\Ordercustomer;
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
        $resultPage->setActiveMenu('Ktpl_Ordercustomer::a_menu_item6');
        $resultPage->addBreadcrumb(__('Ordercustomer'), __('Ordercustomer'));
        $resultPage->addBreadcrumb(__('Manage Coupon'), __('Manage Coupon'));
        $resultPage->getConfig()->getTitle()->prepend(__('Coupon manager'));
        return $resultPage;
    }    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ACL RULE HERE');
    }            
        
}
