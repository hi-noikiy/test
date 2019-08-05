<?php

namespace Magenest\Salesforce\Controller\Adminhtml\Map;

use Magenest\Salesforce\Model\MapFactory;
use Magento\Framework\Controller\ResultFactory;
use Magenest\Salesforce\Controller\Adminhtml\Map as MapController;

class SaveMapping extends MapController
{

    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if ($this->getRequest()->isAjax()) {
            $data = $this->getRequest()->getPostValue();
            if (array_key_exists('result', $data)) {
                $results = $data['result'];
            } else {
                return $controllerResult;
            }


            foreach ($results as $result) {
                $curRow = $this->getCurrentRow($data['type'], $result['key']);
                $curRow->setSalesforce($result['value']);
                $curRow->setMagento($result['key']);
                $curRow->setDescription($result['description']);
                $curRow->setStatus($result['status']);

                if (!$curRow->getId()) {
                    $curRow->setType($data['type']);
                }

                $curRow->save();
                $session->setPageData(false);
            }

            $controllerResult->setData(true);
            return $controllerResult;
        } else {
            return $this->_redirect('*/*/newmapping');
        }
    }

    /**
     * check and return mapMpdel if not update
     *
     * @param $type
     * @param $magentoField
     * @return mixed \Magenest\Salesforce\Model\Map | null
     *
     */
    public function getCurrentRow($type, $magentoField)
    {
        $mapCollection = $this->_mapFactory->create()->getCollection();
        $magento = $mapCollection->addFieldToFilter('type', $type)
            ->addFieldToFilter('magento', $magentoField)
            ->getFirstItem();
        return $magento;

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