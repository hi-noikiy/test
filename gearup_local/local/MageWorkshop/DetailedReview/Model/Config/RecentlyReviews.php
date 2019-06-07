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
class MageWorkshop_DetailedReview_Model_Config_RecentlyReviews
{
    /** @var array $_options */
    protected $_options = array();
    /** @var array $_availableOptions */
    protected $_availableOptions = null;

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (empty($this->_options)) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'none',            'label'=> $helper->__('None')),
                array('value'=>'general',           'label'=> $helper->__('General')),
                array('value'=>'in_category',      'label'=> $helper->__('In Category'))
            );
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function getAvailableOptions()
    {
        if (!isset($this->_availableOptions)) {
            $this->_availableOptions = array();
            $availableSetting = explode(',', Mage::getStoreConfig('detailedreview/category_options/recent_reviews'));
            foreach ($this->toOptionArray(false) as $option) {
                if (in_array($option['value'], $availableSetting)) {
                    $this->_availableOptions[$option['value']] = $option['label'];
                }
            }
        }
        return $this->_availableOptions;
    }

}
