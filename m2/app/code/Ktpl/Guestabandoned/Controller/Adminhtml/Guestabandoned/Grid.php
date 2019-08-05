<?php

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

class Grid extends \Magento\Backend\App\Action {

    protected function _isAllowed() {
        return true; //$this->_authorization->isAllowed('Ktpl_Guestabandoned::Guestabandoned');
    }

    public function execute() {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

}
