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
class Mage_Drcore_Model_Category_Attribute_Source_Ratings
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_optionsArray = array();

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $helper = Mage::helper('core');
        $store = null;

        try {
            $store = Mage::app()->getRequest()->getParam('store') ?: Mage::app()->getStore();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if (!is_object($store)) {
            $store = Mage::getModel('core/store')->load($store);
        }

        if (empty($this->_optionsArray) && is_object($store) && $store->getId()) {
            if ($helper->isModuleEnabled('MageWorkshop_DRCategoryRatings')) {
                /** @var Mage_Rating_Model_Resource_Rating_Collection $collection */
                $collection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->setStoreFilter($store->getId())
                    ->addEntityFilter(Mage::registry('entityId'));

                /** @var Mage_Rating_Model_Rating $item */
                foreach ($collection as $item) {
                    $this->_optionsArray[] = array(
                        'label' => $item->getRatingCode(),
                        'value' => $item->getRatingId()
                    );
                }
                array_unshift($this->_optionsArray, array('value' => '0', 'label' => Mage::helper('catalog')->__('No Ratings')));
            }
        }

        return $this->_optionsArray;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
