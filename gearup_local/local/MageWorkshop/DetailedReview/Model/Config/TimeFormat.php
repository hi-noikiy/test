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
class MageWorkshop_DetailedReview_Model_Config_TimeFormat
{
    /** @var array $_options */
    protected $_options = array();

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'HH:mm',        'label'=> $helper->__('HH:mm')),
                array('value'=>'HH:mm:ss',     'label'=> $helper->__('HH:mm:ss')),
                array('value'=>'hh:mm a',      'label'=> $helper->__('hh:mm a')),
                array('value'=>'hh:mm:ss a',   'label'=> $helper->__('hh:mm:ss a'))
            );
        }

        return $this->_options;
    }
}
