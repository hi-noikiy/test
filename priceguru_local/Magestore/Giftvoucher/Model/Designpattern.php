<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @module     Giftvoucher
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Giftvoucher Designpattern Model
 * 
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */

class Magestore_Giftvoucher_Model_Designpattern extends Varien_Object
{

    const PATTERN_LEFT = 1;
    const PATTERN_TOP = 2;
    const PATTERN_CENTER = 3;
    const PATTERN_SIMPLE = 4;
    const PATTERN_AMAZON = 5;

    /**
     * Get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::PATTERN_LEFT => Mage::helper('giftvoucher')->__('Left'),
            self::PATTERN_TOP => Mage::helper('giftvoucher')->__('Top'),
            self::PATTERN_CENTER => Mage::helper('giftvoucher')->__('Center'),
            self::PATTERN_SIMPLE => Mage::helper('giftvoucher')->__('Simple'),
            self::PATTERN_AMAZON => Mage::helper('giftvoucher')->__('Amazon Gift Card Style')
        );
    }

    /**
     * @return array
     */
    static public function getOptions()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    /**
     * @return array
     */
    static public function getOnlyNewTemplate()
    {
        return array(
            array(
                'value' => self::PATTERN_AMAZON,
                'label' => Mage::helper('giftvoucher')->__('Amazon Gift Card Style')
            )
        );
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return self::getOptions();
    }

}
