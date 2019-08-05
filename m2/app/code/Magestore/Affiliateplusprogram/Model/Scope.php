<?php

/**
 * Magestore.
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Model;

/**
 * Class Scope
 * @package Magestore\Affiliateplusprogram\Model
 */
class Scope extends \Magento\Framework\DataObject
{
    const SCOPE_GLOBAL		= '0';
    const SCOPE_GROUPS		= '1';
    const SCOPE_CUSTOMER	= '2';

    /**
     * @return array
     */
    static public function getOptionArray(){
        return array(
            self::SCOPE_GLOBAL		=> __('All Affiliates'),
            self::SCOPE_GROUPS		=> __('Customer Groups'),
            self::SCOPE_CUSTOMER	=> __('Assigned Affiliates'),
        );
    }

    /**
     * @return array
     */
    static public function getOptions(){
        $options = array();
        foreach (self::getOptionArray() as $value=>$label)
            $options[] = [
                'value'	=> $value,
                'label'	=> $label
            ];
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray(){
        return self::getOptions();
    }
}