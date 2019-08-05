<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplusprogram\Observer\AbtractObserver;

class ProductGetFinalPrice extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        $discountedObj = $observer->getEvent()->getDiscountedObj();
        $affiliateInfo = $this->_cookieHelper->getAffiliateInfo();
        if (!$affiliateInfo) {
            $accountCode = $this->getRequest()->getParam('acc');
            $account = $this->_accountFactory->create()
                    ->setStoreId($this->getStore()->getId())
                    ->loadByIdentifyCode($accountCode);
            $affiliateInfo[$accountCode] = array(
                'index' => '1',
                'code' => $accountCode,
                'account' => $account,
            );
        }
        foreach ($affiliateInfo as $info){
            if ($account = $info['account']) {
                $program = $this->_helper->getProgramByProductAccount($product, $account);
                if ($program) {
                    $price = $discountedObj->getPrice();
                    $discountType = $program->getDiscountType();
                    $discountValue = $program->getDiscount();
                    if ($this->_cookieHelper->getNumberOrdered()) {
                        if ($program->getSecDiscount()) {
                            $discountType = $program->getSecDiscountType();
                            $discountValue = $program->getSecondaryDiscount();
                        }
                    }
                    if ($discountType == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_ITEM
                        || $discountType == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_CART
                    ) {
                        $price -= floatval($discountValue);
                    } elseif ($discountType == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_PERCENTAGE) {
                        $price -= floatval($discountValue) / 100 * $price;
                    }
                    if ($price < 0){
                        $price = 0;
                    }
                    $discountedObj->setPrice($price);
                    $discountedObj->setDiscounted(true);
                }
                return $this;
            }
        }
        return $this;
    }
}