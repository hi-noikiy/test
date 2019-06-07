<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Mobile3
 * @version    3.0.6
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile3_Block_Checkout_Cart_Item_Configure extends Mage_Core_Block_Template
{
    const CART_ITEM_CONFIGURE_BLOCK_PATH = "checkout/cart_item_configure";

    protected $blockCartItemConfigure = null;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::helper('aw_mobile3')->isCartItemConfigureBlockExists()) {
            $this->blockCartItemConfigure = $this->getLayout()->createBlock($this::CART_ITEM_CONFIGURE_BLOCK_PATH);
        }
    }

    protected function _toHtml()
    {
        return '';
    }
}