<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if((string) Mage::getConfig()->getModuleConfig('EcommerceTeam_Sln')->active == 'true') {

    class MageWorx_SeoBase_Block_Page_Html_Pager_Abstract extends EcommerceTeam_Sln_Block_Page_Pager
    {

    }
}
else {

    class MageWorx_SeoBase_Block_Page_Html_Pager_Abstract extends Mage_Page_Block_Html_Pager
    {

    }

}