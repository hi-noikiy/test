<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    tip
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class Gearup_Blog_Block_Rating extends Mage_Core_Block_Template {

    public function getRatings() {
        $ratingCollection = Mage::getModel('rating/rating')
                ->getResourceCollection();
        $ratingCollection->getSelect()
                ->join('rating_entity', 'main_table.entity_id=rating_entity.entity_id and rating_entity.entity_code like "'.Gearup_Blog_Model_RateAggregate::BLOG_RATING_ENTITY.'" ', array('entity_code'));

        $ratingCollection->setPositionOrder()
                ->addRatingPerStoreName(0)
                ->setStoreFilter(0)
                ->load()
                ->addOptionToItems();
        return $ratingCollection;
    }
    
    /**
     * 
     * @param type $postId
     * @param type $customerId
     * @return boolean
     */
    public function isRated($postId) {
        $ratingCount = Mage::getModel('rating/rating_option_vote')->getResourceCollection()
        ->addFieldToFilter(['customer_id','remote_ip'], [
        ['eq' => Mage::getSingleton('customer/session')->getCustomerId()],
        ['eq' => Mage::helper('core/http')->getRemoteAddr()],
        ])
        ->addFieldToFilter('entity_pk_value', $postId)
        ->addFieldToFilter('rating_id', $this->_getRatingId());
               
        if ($ratingCount->count() == 1)
            return true;
        return false;
    }

    private function _getRatingId() {
        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT rating_id FROM ' . $resource->getTableName('rating') . ' '
                . 'WHERE entity_id = (SELECT entity_id FROM ' . $resource->getTableName('rating_entity') . ' '
                . 'WHERE entity_code = "'.Gearup_Blog_Model_RateAggregate::BLOG_RATING_ENTITY.'")';      
        return $readConnection->fetchOne($query);
    }

}
