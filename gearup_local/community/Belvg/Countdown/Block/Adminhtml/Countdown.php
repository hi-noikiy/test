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

class Belvg_Countdown_Block_Adminhtml_Countdown extends  Mage_Adminhtml_Block_Template
{
    /**
     * Get 'Countdown' object, filtered by type and entity id
     *
     * @param string
     * @param int Entity Id (example: if $type is 'product', $entity_id is Product id)
     * @return Belvg_Countdown_Model_Countdown
     */
    public function getCountdown($type, $entity_id)
    {
        $countdown = Mage::getModel('countdown/countdown')->getCountdown($type, $entity_id);

        $datetime        = array();
        $datetime['on']  = $this->datetimeExplode($countdown->getExpireDatetimeOn());
        $datetime['off'] = $this->datetimeExplode($countdown->getExpireDatetimeOff());
        $countdown->setDatetime($datetime);

        return $countdown;
    }

    /**
     * Divides `datetime` type to `date` and `time` separately
     *
     * @param string Datetime
     * @return array
     */
    private function datetimeExplode($datetime)
    {
        if ($datetime) {
            $dt       = explode(' ', $datetime);
            $time     = explode(':', $dt[1]);
            $datetime = array();
            if ($dt[0] == '0000-00-00' || $dt[0] == '') {
                $datetime['date'] = '';
                $datetime['time'] = '';
            } else {
                $datetime['date'] = $dt[0];
                $datetime['time'] = $time[0].':'.$time[1];
            }

            return $datetime;
        }
    }
}