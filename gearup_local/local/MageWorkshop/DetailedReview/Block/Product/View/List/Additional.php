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
class MageWorkshop_DetailedReview_Block_Product_View_List_Additional extends Mage_Core_Block_Text
{
    /** @var Mage_Review_Model_Review $_currentItem */
    protected $_currentItem;

    /** @var Varien_Data_Collection_Db $_reviewCollection */
    protected $_reviewCollection;

    /** @var array $_reviewIds */
    protected $_reviewIds;

    /**
     * @return Mage_Review_Model_Review
     */
    public function getCurrentItem()
    {
        return $this->_currentItem;
        // @todo add throw exception if no data is set to provide to the child blocks
    }

    /**
     * @param Mage_Review_Model_Review $currentItem
     * @return $this
     */
    public function setCurrentItem(Mage_Review_Model_Review $currentItem)
    {
        $this->_currentItem = $currentItem;
        return $this;
    }

    /**
     * @return array
     */
    public function getReviewIds()
    {
        return $this->_reviewIds;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setReviewIds($ids)
    {
        $this->_reviewIds = $ids;
        return $this;
    }

    /**
     * @return Varien_Data_Collection_Db
     */
    public function getReviewCollection()
    {
        return $this->_reviewCollection;
        // @todo add throw exception if no data is set to provide to the child blocks
    }

    /**
     * @param Varien_Data_Collection_Db $reviewCollection
     * @return $this
     */
    public function setReviewCollection(Varien_Data_Collection_Db $reviewCollection)
    {
        $this->_reviewCollection = $reviewCollection;
        return $this;
    }

    protected function _toHtml()
    {
        $this->setText('');
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
            }
            $block->addData(
                array(
                    'review_collection' => $this->getReviewCollection(),
                    'current_item'      => $this->getCurrentItem()
                )
            );
            $this->addText($block->toHtml());
        }
        return parent::_toHtml();
    }
}
