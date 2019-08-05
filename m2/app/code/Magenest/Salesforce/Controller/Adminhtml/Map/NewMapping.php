<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Map;

use Magenest\Salesforce\Controller\Adminhtml\Map;

class NewMapping extends Map
{

    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Mapping Management'));
        return $resultPage;
    }

    /**
     * Check ACL
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::mapping');
    }
}