<?php

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

class Index extends \Magento\Backend\App\Action {

    protected $resultLayoutFactory;

    const ADMIN_RESOURCE = 'Ktpl_Guestabandoned::Guestabandoned';

    protected $resultPageFactory;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, 
    \Magento\Framework\Registry $coreRegistry,
    \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, 
    \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    protected function _isAllowed() {
        return true; //$this->_authorization->isAllowed('Ktpl_Guestabandoned::Guestabandoned');
    }

    public function execute() {

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->_addContent(
                $this->_view->getLayout()->createBlock('Ktpl\Guestabandoned\Block\Adminhtml\Guestabandoned', 'guestabandoned')
        );
        $resultPage->setActiveMenu('Ktpl_Guestabandoned::Guestabandoned');
        $resultPage->getConfig()->getTitle()->prepend(__('Guest Abandoned'));
        $resultPage->addBreadcrumb(__('Guest Abandoned'), __('Guest Abandoned'));
        $resultPage->addBreadcrumb(__('Guest Abandoned'), __('Orders'));
        
        return $resultPage;
    }

  
}
