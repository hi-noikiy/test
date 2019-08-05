<?php

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

class View extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage);
       // $this->_view->loadLayout();
       // $this->_view->renderLayout();
        return $resultPage;
    }

    protected function initPage($resultPage) {
        $resultPage->setActiveMenu('Ktpl_Guestabandoned::Guestabandoned');
        $resultPage->getConfig()->getTitle()->prepend(__('Guest Abandoned'));

        return $resultPage;
    }

}
