<?php
/**
 * MageWorx
 * MageWorx SeoCrossLinks Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoCrossLinks
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoCrossLinks_Helper_Version extends Mage_Core_Helper_Abstract
{
    public function isEnterpriseSince113()
    {
        $mage = new Mage();
        if (is_callable(array($mage, 'getEdition'))
            && Mage::getEdition() == Mage::EDITION_ENTERPRISE
            && version_compare(Mage::getVersion(), '1.13.0.0', '>=')
        ) {
            return true;
        }
        return false;
    }
}