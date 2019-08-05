<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Amasty\Promo\Model\Storage;

class Item
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->scopeConfig     = $scopeConfig;
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * @param AbstractItem $subject
     * @param $key
     * @param null $value
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetData(AbstractItem $subject, $key, $value = null)
    {
        if (!is_string($key)) {
            return [$key, $value];
        }

        $fields = [
            'price',
            'base_price',
            'custom_price',
            'original_custom_price',
            'price_incl_tax',
            'base_price_incl_tax',
            'row_total',
            'row_total_incl_tax',
            'base_row_total',
            'base_row_total_incl_tax',
        ];

        if (in_array($key, $fields)) {
            if ($this->promoItemHelper->isPromoItem($subject)
                && $this->isFullDiscount($subject)
                && $subject->getNotUsePricePlugin() !== true
            ) {
                if (isset(Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key])) {
                    return [$key, Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key]];
                }

                return [$key, 0];
            }
        }

        return [$key, $value];
    }

    /**
     * @param AbstractItem $item
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFullDiscount(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $buyRequest = $item->getBuyRequest();
        $discount = isset($buyRequest['options']['discount']) ? $buyRequest['options']['discount'] : false;

        return $this->discountCalculator->isFullDiscount($discount);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function aroundRepresentProduct(
        AbstractItem $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($proceed($product)) {
            $productRuleId = $product->getData('ampromo_rule_id');
            $itemRuleId    = $this->promoItemHelper->getRuleId($subject);

            return $productRuleId === $itemRuleId;
        } else {
            return false;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $subject
     * @param \Closure $proceed
     * @param bool $string
     *
     * @return array|mixed|string
     */
    public function aroundGetMessage(
        AbstractItem $subject,
        \Closure $proceed,
        $string = true
    ) {
        $result = $proceed($string);

        if ($this->promoItemHelper->isPromoItem($subject)) {
            $customMessage = $this->scopeConfig->getValue(
                'ampromo/messages/cart_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($customMessage) {
                if ($string) {
                    $result .= __("\n" . $customMessage);
                } else {
                    $result[] = __($customMessage);
                }
            }
        }

        return $result;
    }
}
