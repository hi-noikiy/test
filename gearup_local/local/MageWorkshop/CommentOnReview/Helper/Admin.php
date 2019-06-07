<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_CommentOnReview_Helper_Admin
 */
class MageWorkshop_CommentOnReview_Helper_Admin extends Mage_Core_Helper_Abstract
{
    /**
     * Get Link On Review. Frontend
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getLinkOnReview($row)
    {
        $storeId = $row->getStoreId();
        $store   = Mage::app()->getStore($storeId);

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->setStoreId($storeId)
            ->addFieldToFilter('entity_id', $row->getProductId());

        /** _addUrlRewrite() of the product collection does not work with the store that was set in the collection */
        $productCollection->addUrlRewrite();

        /** @var Mage_Catalog_Model_Product $product */
        $product  = $productCollection->getFirstItem();
        $urlModel = $product->getUrlModel()->getUrlInstance();
        $urlModel->setFragment('rw_' . $row->getReviewId());

        return $product->getUrlInStore(array('_store' => $store));
    }

    /**
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getCommentDetail($row)
    {
        $detail = $row->getDetail();
        return Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_NICKNAME_SUFFIX) . substr($detail, 1);
    }
}
