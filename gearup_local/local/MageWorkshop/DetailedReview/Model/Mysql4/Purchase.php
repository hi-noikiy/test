<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Model_Mysql4_Purchase extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Whether table changes are allowed
     *
     * @var bool
     */
    protected $_allowTableChanges = true;
    
    /**
     * @inherit
     */
    protected function _construct()
    {
        $this->_init('detailedreview/purchase', 'item_id');
    }

    public function loadByAttributes($attributes)
    {
        $adapter = $this->_getReadAdapter();
        $where   = array();
        foreach ($attributes as $attributeCode => $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where(implode(' AND ', $where));

        $binds = $attributes;

        return $adapter->fetchRow($select, $binds);
    }
    
    /**
     * @var $id null|int
     * @throws Exception
     * @return MageWorkshop_DetailedReview_Model_Mysql4_Purchase
     */
    public function updateData($id = null)
    {
        $allowTableChanges = $this->_allowTableChanges;
    
        if ($allowTableChanges) {
            $this->_allowTableChanges = false;
        
            $write  = $this->_getWriteAdapter();
            // read and prepare original order information
            $select = $write->select()
                ->distinct(true)
                 ->from(
                     array('so' => $this->getTable('sales/order')),
                     array('customer_email','created_at', 'store_id')
                 )
                 ->join(
                     array('soi' => $this->getTable('sales/order_item')),
                     'so.entity_id = soi.order_id',
                     array('product_id')
                 )
                ->join( array('p' => $this->getTable('catalog/product')),
                    'p.entity_id = soi.product_id',
                    null
                );
        
            if ($id) {
                $select->where('so.entity_id = ?', $id);
            }
            
            $this->beginTransaction();
    
            try {
                $insertSelect = $write->insertFromSelect(
                    $select,
                    $this->getMainTable(),
                    array('customer_email', 'created_at', 'store_id', 'product_id'),
                    Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
                );
        
                $write->query($insertSelect);
                $this->commit();
        
                if ($allowTableChanges) {
                    $this->_allowTableChanges = true;
                }
            } catch (Exception $e) {
                $this->rollBack();
                if ($allowTableChanges) {
                    $this->_allowTableChanges = true;
                }
                throw $e;
            }
        }
        
        return $this;
    }
}
