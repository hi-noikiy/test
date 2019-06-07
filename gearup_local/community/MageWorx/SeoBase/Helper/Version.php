<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoBase_Helper_Version extends Mage_Core_Helper_Abstract
{
    protected $_enterpriseSince113 = null;

    public function isEnterpriseSince113()
    {
        if (is_null($this->_enterpriseSince113)) {
            $mage = new Mage();
            if (is_callable(array($mage, 'getEdition')) && Mage::getEdition() == Mage::EDITION_ENTERPRISE
                && version_compare(Mage::getVersion(), '1.13.0.0', '>=')) {
                $this->_enterpriseSince113 = true;
            }
            else {
                $this->_enterpriseSince113 = false;
            }
        }
        return $this->_enterpriseSince113;
    }
}