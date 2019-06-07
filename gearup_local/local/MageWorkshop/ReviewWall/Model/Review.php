<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_ReviewWall_Model_Review
 */
class MageWorkshop_ReviewWall_Model_Review extends Mage_Review_Model_Review
{
    const IMAGE_FILTER = 'image';

    /** @var array Prepared Reviews Array */
    protected $_preparedReviews = null;

    /**
     * Get products collection by review IDs
     *
     * @param $productIds
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsCollectionByIds($productIds)
    {
        /** @var Mage_Catalog_Model_Product_Visibility $productVisibleModel */
        $productVisibleModel = Mage::getModel('catalog/product_visibility');

        $productCollection = Mage::getModel('catalog/product')->getCollection();

        $productCollection
            ->addUrlRewrite()
            ->addWebsiteFilter(Mage::app()->getStore()->getWebsiteId())
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->addAttributeToFilter('visibility', array('in' => $productVisibleModel->getVisibleInCatalogIds()))
            ->addAttributeToSelect('url_path')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            );

        return $productCollection;
    }

    /**
     * Get prepared reviews collection by store
     *
     * @param $byFilter string|null
     * @return MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     */
    public function getPreparedReviewsCollection($byFilter = null)
    {
        if (!$this->_preparedReviews) {

            /** @var MageWorkshop_DetailedReview_Model_Review $reviewModel */
            $reviewModel = Mage::getModel('detailedreview/review');

            /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $preparedReviews */
            $reviewCollection = $reviewModel->getCollection()
                ->addFieldToFilter(
                    'entity_id',
                    array('eq' => $reviewModel->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                );

            /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $preparedReviews */
            $preparedReviews = $this->applyFilters($reviewCollection, $byFilter);

            $productIds = array();

            /** @var MageWorkshop_DetailedReview_Model_Review $review */
            foreach ($preparedReviews as $review) {
                $productIds[] = $review->getEntityPkValue();
            }

            $productCollection = $this->getProductsCollectionByIds(array_unique($productIds));

            /** @var MageWorkshop_DetailedReview_Model_Review $_review */
            foreach ($preparedReviews as $_review) {
                /** @var Mage_Catalog_Model_Product $_product */
                foreach ($productCollection as $_product) {
                    if ($_product->getId() == $_review->getEntityPkValue()) {
                        $_review->setData('product', $_product);
                    }
                }

                if ($images = $_review->getImage()) {
                    $reviewImage = explode(',', $images);
                    $image = Mage::helper('detailedreview')->getResizedImage($reviewImage[0], 200, null, 90);
                    $_review->setImage($image);
                }

                if (!$_review->hasProduct()) {
                    $preparedReviews->removeItemByKey($_review->getId());
                }
            }

            $this->_preparedReviews = $preparedReviews;
        }

        return $this->_preparedReviews;
    }

    /**
     * Apple filters to reviews collection
     *
     * @param $collection MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     * @param $byFilter string|null
     * @return MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection|array
     */
    public function applyFilters($collection, $byFilter = null)
    {
        $params = Mage::app()->getRequest()->getParams();

        $collection
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setDateOrder()
            ->setPageSize(Mage::getStoreConfig('reviewwall/reviewwall_settings/reviewwall_count_on_page'));

        if (
            $byFilter == self::IMAGE_FILTER
            || (isset($params['filter']) && ($params['filter'] == self::IMAGE_FILTER))
        ) {
            $collection->addFieldToFilter('image', array('neq' => ''));
        }

        if (isset($params['p']) && $params['p']) {

            if ($params['p'] > $collection->getLastPageNumber()) {
                return array();
            }

            $collection->setCurPage($params['p']);
        }

        if (isset($params['keywords']) && $params['keywords']) {
            $collection->addKeywordsFilter($params['keywords']);
        }

        $collection->addHelpfulInfo();
        $collection->addRateVotes();

        return $collection;
    }
}
