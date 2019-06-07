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

/**
 * Class MageWorkshop_DetailedReview_Model_Review_Products_List
 */
class MageWorkshop_DetailedReview_Model_Review_Products_List extends Mage_Core_Model_Abstract
{

    public  function _construct()
    {
        $this->_init('detailedreview/review_products_list');
    }

    /**
     * @param $orderId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCurrentOrderProducts($orderId)
    {
        $productCollection = Mage::helper('detailedreview')
            ->getProductsByOrders($orderId, Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));
        $productCollection->addAttributeToSelect('*');
        return $productCollection;
    }
    /**
     * @param Varien_Object $customerIdentifier
     * @param Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsForReview(Varien_Object $customerIdentifier, Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        if ($customerIdentifier->getCustomerId() || $customerIdentifier->getCustomerEmail()) {
            $orderCollection->addFieldToFilter('customer_id', $customerIdentifier->getCustomerId());
            $orderCollection->addFieldToFilter('customer_email', $customerIdentifier->getCustomerEmail());
        }

        $orderCollection->addFieldToFilter('entity_id', array('neq' => $customerIdentifier['order_id']));

        if (count($orderCollection->getAllIds()) == 0) {
            return false;
        }
        $productCollection = Mage::helper('detailedreview')->getProductsByOrders($orderCollection->getAllIds());

        $productCollection->addFieldToFilter('entity_id', array('nin' => $originalOrderProductCollection->getAllIds()));
        return $productCollection;
    }
}
