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

class Belvg_Countdown_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PRODUCT_LIST_CONTAINER_PREFIX = 'product-list-container';

    /**
     * Javascript timestamp converter
     *
     * @return string
     */
    public function getTimeLocale()
    {
        /* Example of conversion for languages that have different forms of in plural */
        /*
            $lang = "{
                years:   ['год,',    'года',    'лет'    ],
                months:  ['месяц',   'месяца',  'месяцев'],
                days:    ['день',    'дня',     'дней'   ],
                hours:   ['час',     'часа',    'часов'  ],
                minutes: ['минута',  'минуты',  'минут'  ],
                seconds: ['секунда', 'секунды', 'секунд' ],
                plurar:  function(n){
                    return (n % 10 == 1 && n % 100 != 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2);
                }
            }";
        */
        $lang = "{
                years:   ['" . $this->__('year') . "',   '" . $this->__('years') . "',   '" . $this->__('years_form2') . "'  ],
                months:  ['" . $this->__('month') . "',  '" . $this->__('months') . "',  '" . $this->__('months_form2') . "' ],
                days:    ['" . $this->__('day') . "',    '" . $this->__('days') . "',    '" . $this->__('days_form2') . "'   ],
                hours:   ['" . $this->__('hour') . "',   '" . $this->__('hours') . "',   '" . $this->__('hours_form2') . "'  ],
                minutes: ['" . $this->__('minute') . "', '" . $this->__('minutes') . "', '" . $this->__('minutes_form2') . "'],
                seconds: ['" . $this->__('second') . "', '" . $this->__('seconds') . "', '" . $this->__('seconds_form2') . "'],
                plurar:  function(n){
                    return (" . $this->__('n == 1 ? 0 : 1') . ");
                }
        }";

        return $lang;
    }
    
    public function getTimeLocaleSlashed()
    {
        return addcslashes(preg_replace("/(\r?\n)/", '', $this->getTimeLocale()), "\'\"");
    }

    /**
     * Is the extension enabled?
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('countdown/settings/enabled', Mage::app()->getStore());
    }

    /**
     * Home Page - Number of Products
     *
     * @return int
     */
    public function getProductsCount()
    {
        return (int) Mage::getStoreConfig('countdown/settings/productscount', Mage::app()->getStore());
    }

    /**
     * Home Page - Number of Categories
     *
     * @return int
     */
    public function getCategoriesCount()
    {
        return (int) Mage::getStoreConfig('countdown/settings/categoriescount', Mage::app()->getStore());
    }

    /**
     * Returns the massive or line with current time according to $type
     *
     * @param string ('js', 'pre', 'sql')
     * @return mixed
     */
    public function getCurrentTime($type = FALSE)
    {
        $currentTime        = array();
        $currentTime['js']  = date('d F Y H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $currentTime['pre'] = date('d F Y',       Mage::getModel('core/date')->timestamp(time()));
        $currentTime['sql'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        if ($type) {
            return $currentTime[$type];
        } else {
            return $currentTime;
        }
    }

    /**
     * Enabling or disabling the object, bound to this countdown
     *
     * @param string Datetime of enabling
     * @param string Datetime of disabling
     * @return boolean
     */
    public function getEntityEnabled($on, $off)
    {
        $currentTime = $this->getCurrentTime('sql');
        if ($on < $off) {
            if ($on <= $currentTime && $currentTime < $off) {
                $enabled = 0;
            } else {
                $enabled = 1;
            }
        } else {
            if ($off <= $currentTime && $currentTime < $on) {
                $enabled = 1;
            } else {
                $enabled = 0;
            }
        }

        return $enabled;
    }

    /**
     * Validate entered date
     *
     * @param string Date
     * @return boolean
     */
    private function valid_date($str)
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $str)) {
            $arr  = explode("-", $str);
            $yyyy = $arr[0];
            $mm   = $arr[1];
            $dd   = $arr[2];
            if (is_numeric($yyyy) && is_numeric($mm) && is_numeric($dd)) {
                if (checkdate($mm, $dd, $yyyy)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Validate entered time
     *
     * @param string Time
     * @return boolean
     */
    private function valid_time($str)
    {
        if (preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $str)) {
            $arr = explode(":", $str);
            $hh  = $arr[0];
            $mm  = $arr[1];
            $ss  = $arr[2];
            if (is_numeric($hh) && is_numeric($mm) && is_numeric($ss)) {
                if ($this->checktime($hh, $mm, $ss)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Check hrs/mins/secs
     *
     * @param int Hour
     * @param int Minute
     * @param int Second
     * @return boolean
     */
    private function checktime($hour, $minute, $second)
    { 
        if($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
            return TRUE;
        }

        return FALSE;
    }

    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Validate the entered date and time
     * Converts the entered data to datetime for DB storage
     *
     * @param string 'on' / 'off' (entered date and time for enabling/disabling)
     * @return string
     */
    public function getPostDatetime($a)
    {
        $post = $this->_getRequest()->getPost();
        $date = $post['countdown-date-'.$a];        // 2011-08-11
        $time = $post['countdown-time-'.$a].':00';  // 11:07:00
        if (!$this->valid_date($date)) {
            $date = '0000-00-00';
        }

        if (!$this->valid_time($time)) {
            $time = '00:00:00';
        }

        return $date.' '.$time;
    }

    public function getListSettings()
    {
        return array();
    }
}