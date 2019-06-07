<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_DRReminder_Model_Review_Reminder_List
 */
class MageWorkshop_DRReminder_Model_Review_Reminder_List extends Mage_Core_Model_Abstract
{

    public  function _construct()
    {
        $this->_init('drreminder/review_reminder_list');
    }

    /**
     * @param MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier
     * @param Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsToReview(
        MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier,
        Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
    )
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        if ($customerIdentifier) {
        $orderCollection->addFieldToFilter(MageWorkshop_DRReminder_Model_CustomerIdentifier::IDENTIFIER_TYPE_ID, MageWorkshop_DRReminder_Model_CustomerIdentifier::IDENTIFIER_TYPE_EMAIL)
            ->addFieldToFilter('entity_id', array('neq' => $customerIdentifier->getOrderId()));
        } else {
            return false;
        }

        if (count($orderCollection->getAllIds()) == 0) {
            return false;
        }
        $productCollection = Mage::helper('drreminder')->getProductsByOrders($orderCollection->getAllIds(), false, Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));
        if(count($originalOrderProductCollection->getAllIds())) {
            $productCollection->addFieldToFilter('entity_id', array('nin' => $originalOrderProductCollection->getAllIds()));
        }
        $this->_addNotReviewedFilterToCollection($productCollection, $customerIdentifier);
        return $productCollection;
    }


    /**
     * @param MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getNewProductsToReview(MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier)
    {
        $productCollection = Mage::helper('drreminder')->getProductsByOrders($customerIdentifier->getOrderId(), false, Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));
        $productCollection->addAttributeToSelect('*');
        $this->_addNotReviewedFilterToCollection($productCollection);
        return $productCollection;
    }

    /**
     * @param $productCollection Mage_catalog_Model_Resource_Product_Collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addNotReviewedFilterToCollection($productCollection)
    {
        $code = Mage_Review_Model_Review::ENTITY_PRODUCT_CODE;
        $reviewCollection = Mage::getModel('review/review')->getCollection();
        $this->_setResourceModel('review/review');
        $reviewCollection
            ->addFieldToFilter(MageWorkshop_DRReminder_Model_CustomerIdentifier::IDENTIFIER_TYPE_ID, MageWorkshop_DRReminder_Model_CustomerIdentifier::IDENTIFIER_TYPE_EMAIL)
            ->getSelect()
            ->joinInner(
                array('re' => $this->getResource()->getTable('review/review_entity')),
                "main_table.entity_id = re.entity_id AND re.entity_code = '$code'",
                array()
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_pk_value');

        $productIds =  $reviewCollection->getColumnValues('entity_pk_value');

        if ($productIds) {
            $productCollection->addFieldToFilter('entity_id', array('nin' => $productIds));
        }

        return $productCollection;
    }
}
