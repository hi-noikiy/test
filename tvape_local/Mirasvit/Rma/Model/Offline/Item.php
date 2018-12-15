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



/**
 * @method Mirasvit_Rma_Model_Resource_Item_Collection|Mirasvit_Rma_Model_Item[] getCollection()
 * @method Mirasvit_Rma_Model_Item load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Item setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Item setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Item getResource()
 * @method int getReasonId()
 * @method Mirasvit_Rma_Model_Item setReasonId(int $reasonId)
 * @method int getResolutionId()
 * @method Mirasvit_Rma_Model_Item setResolutionId(int $resolutionId)
 * @method int getConditionId()
 * @method Mirasvit_Rma_Model_Item setConditionId(int $conditionId)
 * @method int getRmaId()
 * @method Mirasvit_Rma_Model_Item setRmaId(int $rmaId)
 * @method Mirasvit_Rma_Model_Item setQtyRequested(int $qty)
 * @method int getQtyRequested()
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $param)
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $param)
 * @method string getName()
 * @method $this setName(string $param)
 * @method int getOfflineOrderId()
 * @method $this setOfflineOrderId(int $param)
 */
class Mirasvit_Rma_Model_Offline_Item extends Mirasvit_Rma_Model_ItemAbstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/offline_item');
    }

    /**
     * @var Mirasvit_Rma_Model_Offline_Order
     */
    protected $_order;

    /**
     * @return Mirasvit_Rma_Model_Offline_Order
     */
    public function getOfflineOrder()
    {
        if (!$this->_order) {
            $this->_order = Mage::getModel('rma/offline_order')->load($this->getOfflineOrderId());
        }

        return $this->_order;
    }

}