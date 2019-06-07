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
class MageWorkshop_DetailedReview_Model_Config_DateFormat
{
    /** @var array $_options */
    protected $_options = array();

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (empty($this->_options)) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'DD/MM/YYYY', 'label'=> $helper->__('DD/MM/YYYY')),
                array('value'=>'DD-MM-YYYY', 'label'=> $helper->__('DD-MM-YYYY')),
                array('value'=>'DD MM YYYY', 'label'=> $helper->__('DD MM YYYY')),
                array('value'=>'MM/DD/YYYY', 'label'=> $helper->__('MM/DD/YYYY')),
                array('value'=>'MM-DD-YYYY', 'label'=> $helper->__('MM-DD-YYYY')),
                array('value'=>'MM DD YYYY', 'label'=> $helper->__('MM DD YYYY')),
                array('value'=>'YYYY-MM-DD',  'label'=> $helper->__('YYYY-MM-DD')),
                array('value'=>'MMM DD YYYY', 'label'=> $helper->__('MMM DD YYYY')),
            );
        }

        return $this->_options;
    }
}
