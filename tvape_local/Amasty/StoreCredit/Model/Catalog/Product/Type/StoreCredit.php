<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_STORECREDIT_PRODUCT = 'amstcred';

    /**
     * Is a configurable product type
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Whether product quantity is fractional number or not
     *
     * @var bool
     */
    protected $_canUseQtyDecimals = false;

    public function isStoreCredit($product = null)
    {
        return true;
    }

    /**
     * Check is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product = null)
    {
        return true;
    }

    /**
     * Check if product is available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return false;
        }

        $prices = $this->getProduct($product)->getPriceModel()->getAmounts($product);
        $open = $this->getProduct($product)->getAmstcredAllowOpenAmount();

        if (!$open && !$prices) {
            return false;
        }

        /*if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }*/

        return parent::isSalable($product);
    }

    public function getIsSalable($product = null)
    {
        return $this->isSalable($product);
    }

    public function canConfigure($product = null)
    {
        $prices = $this->getProduct($product)->getPriceModel()->getAmounts($product);
        $open = $this->getProduct($product)->getAmstcredAllowOpenAmount();
        if (!$open && count($prices) == 1) {
            return false;
        }

        return true;
    }


    /**
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = array();
        foreach ($this->_customFields() as $field => $data) {
            $options[$field] = $buyRequest->getData($field);
        }
        return $options;
    }

    /**
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product = null)
    {
        parent::checkProductBuyState($product);
        $product = $this->getProduct($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $buyRequest = new Varien_Object(unserialize($option->getValue()));
            $this->_validate($buyRequest, $product, self::PROCESS_MODE_FULL);
        }
        return $this;
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function beforeSave($product = null)
    {
        parent::beforeSave($product);
        $this->getProduct($product)->setTypeHasOptions(true);
        $this->getProduct($product)->setTypeHasRequiredOptions(true);
        return $this;
    }

    public function hasRequiredOptions($product = null)
    {
        $prices = $this->getProduct($product)->getPriceModel()->getAmounts($product);
        $open = $this->getProduct($product)->getAmstcredAllowOpenAmount();
        if (!$open && count($prices) == 1) {
            return false;
        }

        return true;
    }


    /**
     * Prepare product and its configuration to be added to some products list.
     * Use standard preparation process and also add specific options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        try {
            $amount = $this->_validate($buyRequest, $product, $processMode);
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('amstcred')->__('An error has occurred while preparing Store Credit.');
        }

        $product->addCustomOption('amstcred_amount', $amount, $product);

        foreach ($this->_customFields() as $field => $data) {
            if (in_array($field, array('amstcred_amount', 'amstcred_amount_custom'))) {
                continue;
            }
            $product->addCustomOption($field, $buyRequest->getData($field), $product);
        }

        return $result;

    }

    /**
     *
     * @param Varien_Object $buyRequest
     * @param  $product
     * @param  $processMode
     * @return double|float|mixed
     */
    private function _validate(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $currentProduct = Mage::getModel('catalog/product')->load($product->getId());

        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        /* @var $_helper Amasty_StoreCredit_Helper_Data */
        $_helper = Mage::helper('amstcred');

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::throwException(
                $_helper->__('Please login to purchase!')
            );
        }


        $allowedAmounts = array();
        $minCustomAmount = $currentProduct->getAmstcredOpenAmountMin();
        $maxCustomAmount = $currentProduct->getAmstcredOpenAmountMax();

        foreach ($this->getProduct($product)->getPriceModel()->getAmounts($product) as $value) {
            $itemAmount = Mage::app()->getStore()->roundPrice($value['website_value']);
            $allowedAmounts[$itemAmount] = $itemAmount;
        }

        $isAmountCustom = $currentProduct->getAmstcredAllowOpenAmount() && ($buyRequest->getAmstcredAmount() == 'custom' || count($allowedAmounts) == 0);

        if ($isStrictProcessMode) {
            $listErrors = array();
            $listFields = $this->_customFields();
            $listFields['amstcred_amount']['isCheck'] = !(count($allowedAmounts) == 1) && !$isAmountCustom;
            $listFields['amstcred_amount_custom']['isCheck'] = $isAmountCustom;

            foreach ($listFields as $field => $data) {
                $isCheck = isset($data['isCheck']) ? $data['isCheck'] : true;
                if (!$buyRequest->getData($field) && $isCheck) {
                    $listErrors[] = $_helper->__('Please specify %s', $data['fieldName']);
                }
            }
            $countErrors = count($listErrors);
            if ($countErrors > 1) {
                Mage::throwException(
                    $_helper->__('Please specify all the required information.')
                );
            } elseif ($countErrors) {
                Mage::throwException(
                    $listErrors[0]
                );
            }
        }


        $amount = null;
        if ($isAmountCustom) {
            if ($minCustomAmount && $minCustomAmount > $buyRequest->getAmstcredAmountCustom() && $isStrictProcessMode) {
                $minCustomAmountText = Mage::helper('core')->currency($minCustomAmount, true, false);
                Mage::throwException(
                    Mage::helper('amstcred')->__('Store Credit min amount is %s', $minCustomAmountText)
                );
            }

            if ($maxCustomAmount && $maxCustomAmount < $buyRequest->getAmstcredAmountCustom() && $isStrictProcessMode) {
                $maxCustomAmountText = Mage::helper('core')->currency($maxCustomAmount, true, false);
                Mage::throwException(
                    Mage::helper('amstcred')->__('Store Credit max amount is %s', $maxCustomAmountText)
                );
            }

            if ($buyRequest->getAmstcredAmountCustom() <= 0 && $isStrictProcessMode) {
                Mage::throwException(
                    $_helper->__('Please specify Store Credit Value')
                );
            }

            if (
                (!$minCustomAmount || ($minCustomAmount <= $buyRequest->getAmstcredAmountCustom())) &&
                (!$maxCustomAmount || ($maxCustomAmount >= $buyRequest->getAmstcredAmountCustom())) &&
                $buyRequest->getAmstcredAmountCustom() > 0

            ) {
                $amount = $buyRequest->getAmstcredAmountCustom();

                $rate = Mage::app()->getStore()->getCurrentCurrencyRate();
                if ($rate != 1) {
                    $amount = Mage::app()->getStore()->roundPrice($amount / $rate);
                }
            }
        } else {
            if (count($allowedAmounts) == 1) {
                $amount = array_shift($allowedAmounts);
            } elseif (isset($allowedAmounts[$buyRequest->getAmstcredAmount()])) {
                $amount = $allowedAmounts[$buyRequest->getAmstcredAmount()];
            } elseif ($isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('amstcred')->__('Please specify Store Credit amount.')
                );
            }
        }

        return $amount;
    }


    protected function _customFields()
    {
        return Mage::helper('amstcred')->getStoreCreditFields();
    }
}
