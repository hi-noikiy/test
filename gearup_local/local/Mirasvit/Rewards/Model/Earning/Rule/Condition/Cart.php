<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Model_Earning_Rule_Condition_Cart extends Mage_Rule_Model_Condition_Abstract
{
    const OPTION_COUPON_USED = 'coupon_used';
    const OPTION_COUPON_CODE = 'coupon_code';
    const OPTION_DISCOUNT_AMOUNT = 'discount_amount';
    const OPTION_CURRENT_STORE = 'current_store';
    const OPTION_CURRENCY_USED = 'currency_used';

    /**
     * Returns Rewards configuration model.
     *
     * @return Mirasvit_Rewards_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rewards/config');
    }

    /**
     * Loads attributes' values and titles.
     *
     * @return Mirasvit_Rewards_Model_Earning_Rule_Condition_Customer
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            self::OPTION_COUPON_USED => Mage::helper('rewards')->__('Coupon Used'),
            self::OPTION_COUPON_CODE => Mage::helper('rewards')->__('Coupon Code'),
            self::OPTION_DISCOUNT_AMOUNT => Mage::helper('rewards')->__('Discount Amount'),
            self::OPTION_CURRENT_STORE => Mage::helper('rewards')->__('Current Store'),
            self::OPTION_CURRENCY_USED => Mage::helper('rewards')->__('Currency Used'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * Loads input type for current Condition element
     *
     * @return string
     */
    public function getInputType()
    {
        $type = 'string';

        switch ($this->getAttribute()) {

            case self::OPTION_COUPON_USED:
                $type = 'select';
                break;

            case self::OPTION_CURRENT_STORE:
                $type = 'select';
                break;

            case self::OPTION_CURRENCY_USED:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * Loads element type for current Condition value
     *
     * @return string
     */
    public function getValueElementType()
    {
        $type = 'text';

        switch ($this->getAttribute()) {

            case self::OPTION_COUPON_USED:
                $type = 'select';
                break;

            case self::OPTION_CURRENT_STORE:
                $type = 'select';
                break;

            case self::OPTION_CURRENCY_USED:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * Creates option list for select-based Conditions
     *
     * @return Mirasvit_Rewards_Model_Earning_Rule_Condition_Customer
     */
    protected function _prepareValueOptions()
    {
        $selectOptions = array();

        if ($this->getAttribute() === self::OPTION_COUPON_USED) {
            $selectOptions = array(
                array('value' => 0, 'label' => Mage::helper('rewards')->__('No')),
                array('value' => 1, 'label' => Mage::helper('rewards')->__('Yes')),
            );
        }

        if ($this->getAttribute() === self::OPTION_CURRENT_STORE) {
            $selectOptions = Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray();
        }

        if ($this->getAttribute() === self::OPTION_CURRENCY_USED) {
            $selectOptions = Mage::getSingleton('adminhtml/system_config_source_currency')->toOptionArray(false);
        }

        $this->setData('value_select_options', $selectOptions);

        $hashedOptions = array();
        foreach ($selectOptions as $o) {
            $hashedOptions[$o['value']] = $o['label'];
        }
        $this->setData('value_option', $hashedOptions);

        return $this;
    }

    /**
     * Validates current attribute
     *
     * @param Varien_Object $object - current address quote object (in fact - Mage_Sales_Model_Quote_Address)
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $totals = $object->getTotals();

        $validateData = new Varien_Object();
        if ($totals) {
            $totalKeys = array_keys($totals);
            $couponUsed = 0;
            if (in_array('discount', $totalKeys) && !in_array('rewards_spend', $totalKeys)) {
                $couponUsed = 1;
            }

            $validateData
                ->setData(self::OPTION_DISCOUNT_AMOUNT,
                    in_array('discount', $totalKeys) ? $totals['discount']->getValue() : 0)
                ->setData(self::OPTION_COUPON_USED, $couponUsed)
                ->setData(self::OPTION_COUPON_CODE, $object->getQuote()->getCouponCode());
        }

        $validateData
            ->setData(self::OPTION_CURRENT_STORE, Mage::app()->getStore()->getId())
            ->setData(self::OPTION_CURRENCY_USED, Mage::app()->getStore()->getCurrentCurrencyCode())
        ;

        $value = $validateData->getData($this->getAttribute());

        if (($this->getAttribute() == self::OPTION_DISCOUNT_AMOUNT) && substr($this->getValue(), -1) == '%') {
            $percent = substr($this->getValue(), 0, strlen($this->getValue()) - 1) / 100;
            $this->setValue($totals['subtotal']->getValue() * $percent);
            $value = abs($value);
        }

        $res = $this->validateAttribute($value);
        return $res;
    }

    /**
     * Returns value by the option, selected in dialog.
     * Critical for select-based attributes
     *
     * @param string $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option'.(($option !== null) ? '/'.$option : ''));
    }

    /**
     * Returns option list for select-based Condition
     * Critical for select-based attributes
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        return $this->getData('value_select_options');
    }


}