<?php

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

class Refresh extends \Magento\Backend\App\Action {

    protected $_refreshData;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Ktpl\Guestabandoned\Cron\RefreshData $refreshData, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_refreshData = $refreshData;
        parent::__construct($context);
    }

    public function execute() {
        $this->_refreshData->execute();
        $this->_redirect('*/*/index');
    }

}
