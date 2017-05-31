<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Pickuporder;
class Grid extends \Magento\Backend\App\Action
{
    public function execute()
    {
       $this->_view->loadLayout(false);
        $this->_view->getLayout()->getMessagesBlock()->setMessages($this->messageManager->getMessages(true));
        $this->_view->renderLayout();
    }
  	                 
}
