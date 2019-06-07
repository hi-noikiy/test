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
class Mage_Detailedreview_Model_Category_Attribute_Source_Fields
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $helper = Mage::helper('core');
            $this->_options = array(
                array(
                    'label' => Mage::helper('catalog')->__('None'),
                    'value' => 'none'
                ),
                array(
                    'label' => $helper->__('Good and Bad details'),
                    'value' => 'good_and_bad_detail'
                ),
                array(
                    'label' => $helper->__('Pros and Cons'),
                    'value' => 'pros_and_cons'
                ),
                array(
                    'label' => $helper->__('User-defined Pros and Cons'),
                    'value' => 'user_pros_and_cons'
                ),
                array(
                    'label' => $helper->__('Video'),
                    'value' => 'video'
                ),
                array(
                    'label' => $helper->__('Image'),
                    'value' => 'image'
                ),
                array(
                    'label' => $helper->__('Sizing'),
                    'value' => 'sizing'
                ),
                array(
                    'label' => $helper->__('About You Section'),
                    'value' => 'about_you'
                ),
                array(
                    'label' => $helper->__('Body Type Section'),
                    'value' => 'body_type'
                ),
                array(
                    'label' => $helper->__('Response'),
                    'value' => 'response'
                ),
            );
        }
        $options = new Varien_Object($this->_options);
        Mage::dispatchEvent('detailedreview_category_attribute_source_fields', array(
            'options' => $options
        ));
        $this->_options = $options->getData();
        return $this->_options;
    }
}
