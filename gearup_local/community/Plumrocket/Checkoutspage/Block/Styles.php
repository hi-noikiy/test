<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Checkoutspage_Block_Styles extends Mage_Catalog_Block_Product_Abstract
{

    public function getLinkColor()
    {
        return $this->_getDesignSettings('link_color');
    }


    public function getLinkHoverColor()
    {
        return $this->_getDesignSettings('link_hover_color');
    }


    public function getButtonBackgroundColor()
    {
        return $this->_getDesignSettings('button_background_color');
    }


    public function getButtonTextColor()
    {
        return $this->_getDesignSettings('button_text_color');
    }


    public function getButtonHoverBackgroundColor()
    {
        return $this->_getDesignSettings('button_hover_background_color');
    }


    public function getButtonHoverTextColor()
    {
        return $this->_getDesignSettings('button_hover_text_color');
    }


    protected function _getDesignSettings($field)
    {
        return Mage::getStoreConfig('checkoutspage/design/'.$field);
    }

}