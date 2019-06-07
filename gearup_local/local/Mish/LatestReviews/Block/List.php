<?php
/**
 * Products List
 */

class Mish_LatestReviews_Block_List extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * @var Varien_Data_Collection_Db
     */
    protected $_collection;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->setTemplate('latestreviews/list.phtml');
    }

    /**
     * Collection
     *
     * @return Varien_Data_Collection_Db
     */
    public function getCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::getModel('review/review')
                ->getProductCollection()
                ->addAttributeToSelect('thumbnail')
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(1)
                ->setDateOrder()
                ->setPageSize(18);

            $this->_collection->getSelect()
                ->columns(array('reviews_count'=>'count(*)'))
                ->group('entity_pk_value');

            $this->_collection->load()
                ->addReviewSummary();
        }

        return $this->_collection;
    }
} 