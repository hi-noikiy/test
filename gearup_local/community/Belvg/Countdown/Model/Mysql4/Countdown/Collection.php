<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Countdown_Model_Mysql4_Countdown_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('countdown/countdown');
    }
    
    /**
     * Delete all items from collection
     */
    public function delete()
    {
        foreach ($this->getItems() as $k=>$item) {
            $item->delete();
            unset($this->_items[$k]);
        }

        return $this;
    }
    
    /**
     * Adds a filter for collection
     * Select ‘Countdown’ objects, that needs to be enabled
     *
     * @param string (example: 'product' or 'category')
     * @param string Current datetime
     */
    public function addChangeOnFilter($type, $current)
    {
        $this->getSelect()
            ->where("(    (expire_datetime_on > expire_datetime_off AND ('" . $current . "' < expire_datetime_off OR  '" . $current . "' >= expire_datetime_on))
                       OR (expire_datetime_on < expire_datetime_off AND ('" . $current . "' < expire_datetime_off AND '" . $current . "' >= expire_datetime_on))
                     ) AND entity_enabled=0 AND countdown_off=1 AND entity_type='" . $type . "'")
            ->group('entity_id');   
    }

    /**
     * Adds a filter for collection
     * Select ‘Countdown’ objects, that needs to be disabled
     *
     * @param string (example: 'product' or 'category')
     * @param string Current datetime
     */
    public function addChangeOffFilter($type, $current)
    {
        $this->getSelect()
            ->where("(    (expire_datetime_on > expire_datetime_off AND ('" . $current . "' >= expire_datetime_off AND '" . $current . "' < expire_datetime_on))
                       OR (expire_datetime_on < expire_datetime_off AND ('" . $current . "' >= expire_datetime_off OR  '" . $current . "' < expire_datetime_on))
                     ) AND entity_enabled=1 AND countdown_off=1 AND entity_type='" . $type . "'")
            ->group('entity_id');   
    }

}