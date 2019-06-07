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
class Gearup_Blog_Block_Blog extends AW_Blog_Block_Blog {

    protected function _prepareCollection() {
        $searchString = $this->getRequest()->getParam('search');

        if (!$this->getData('cached_collection') || !empty($searchString)) {
            $sortOrder = $this->getCurrentOrder();
            $sortDirection = $this->getCurrentDirection();
            $collection = Mage::getModel('blog/blog')->getCollection()
                    ->addPresentFilter()
                    ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                    ->addStoreFilter()
                    ->joinComments()

            ;
            $collection->getSelect()->joinLeft(
                    array('blog_rating' => 'rating_option_vote_aggregated'), 'main_table.post_id = blog_rating.entity_pk_value and blog_rating.rating_id= ' .
                    new Zend_Db_Expr('(SELECT rating_id FROM  rating WHERE entity_id = ' . new Zend_Db_Expr('(SELECT entity_id FROM rating_entity WHERE  entity_code like "'.Gearup_Blog_Model_RateAggregate::BLOG_RATING_ENTITY.'" )') . ')'), 'percent'
            );


            if ($searchString) {
                $collection->addFieldToFilter(array('title', 'post_content'),
                    array(
                        array('like' => '%' . $searchString . '%'), 
                        array('like' => '%' . $searchString . '%')
                    )
                );
                
                //$collection->addFieldToFilter('title', ['like' => '%' . $searchString . '%']);
            }    

            $collection->setOrder($collection->getConnection()->quote($sortOrder), $sortDirection);
            $collection->setPageSize((int) self::$_helper->postsPerPage());

            if ($searchString)
                return $collection;
           
            $this->setData('cached_collection', $collection);
        }
        return $this->getData('cached_collection');
    }

    public function getAllPostCount()
    {
        $collection = Mage::getModel('blog/blog')->getCollection()
                    ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                    ->addStoreFilter();
                    
        return count($collection);
    }

}
