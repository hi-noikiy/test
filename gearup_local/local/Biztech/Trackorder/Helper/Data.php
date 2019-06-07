<?php

class Biztech_Trackorder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Cookie params
     */
    protected $_cookieName  = 'guest-view';
    protected $_lifeTime    = 600;

    public function getTrackorderUrl()
    {
        return $this->_getUrl('trackorder/index');
    }
}