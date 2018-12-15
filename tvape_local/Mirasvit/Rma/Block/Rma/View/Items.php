<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Rma_View_Items extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'frontend');
        $this->setTemplate('mst_rma/rma/email/items.phtml');
    }

    /**
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getItemsCollection()
    {
        $rma = $this->getRma();
        $collection = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $rma->getId())
            ->addFieldToFilter('qty_requested', array('gt' => 0));
        if ($resolutionId = $this->getResolutionId()) {
            $collection->addFieldToFilter('main_table.resolution_id', $resolutionId);
        }
        if ($reasonId = $this->getReasonId()) {
            $collection->addFieldToFilter('main_table.reason_id', $reasonId);
        }
        if ($conditionId = $this->getConditionId()) {
            $collection->addFieldToFilter('main_table.condition_id', $conditionId);
        }

        return $collection;
    }

    /**
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getOfflineItemsCollection()
    {
        $rma = $this->getRma();
        $collection = Mage::getModel('rma/offline_item')->getCollection()
            ->addFieldToFilter('rma_id', $rma->getId())
            ->addFieldToFilter('qty_requested', array('gt' => 0));
        if ($resolutionId = $this->getResolutionId()) {
            $collection->addFieldToFilter('main_table.resolution_id', $resolutionId);
        }
        if ($reasonId = $this->getReasonId()) {
            $collection->addFieldToFilter('main_table.reason_id', $reasonId);
        }
        if ($conditionId = $this->getConditionId()) {
            $collection->addFieldToFilter('main_table.condition_id', $conditionId);
        }

        return $collection;
    }
}
