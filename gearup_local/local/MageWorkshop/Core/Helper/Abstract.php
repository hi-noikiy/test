<?php

/**
 * Created by PhpStorm.
 * User: maximr
 * Date: 11.11.16
 * Time: 14:28
 */
abstract class MageWorkshop_Core_Helper_Abstract extends Mage_Core_Helper_Abstract
{

    CONST MIN_NAME = '.min';

    protected  $version;

    protected  $store;

    public function __construct()
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = Mage::helper('detailedreview');
        $this->version = $helper->getExtensionVersion();
        $this->store = Mage::app()->getStore();
    }

    /**
     * Check if debug mode enabled
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool)Mage::getStoreConfig('drcore/debug/enable');
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function currentStore()
    {
        return $this->store;
    }

    /**
     * @param $link
     * @return string
     */
    public function builder($link)
    {
        if (Mage::helper('detailedreview/config')->isDetailedReviewEnabled()) {
            $path = sprintf($link, '', $this->version);
            
            if (!$this->isDebugMode()) {
                $path = sprintf($link, $this->version, self::MIN_NAME);
            }
        }
        
        return isset($path) ? $path : null;
    }
}