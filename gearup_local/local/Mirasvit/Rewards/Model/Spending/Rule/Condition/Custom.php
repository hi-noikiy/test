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



class Mirasvit_Rewards_Model_Spending_Rule_Condition_Custom extends Mage_Rule_Model_Condition_Abstract
{

    const OPTION_COUPON_USED = 'coupon_used';
    const OPTION_COUPON_CODE = 'coupon_code';
    const OPTION_DISCOUNT_AMOUNT = 'discount_amount';
    const OPTION_CURRENT_STORE = 'current_store';
    const OPTION_CURRENCY_USED = 'currency_used';

    /**
     * @return Mirasvit_Rewards_Model_Spending_Rule_Condition_Custom
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
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $totals = $object->getTotals();

        $totalKeys = array_keys($totals);
        $couponUsed = 0;
        if (in_array('discount', $totalKeys) && !in_array('rewards_spend', $totalKeys)) {
            $couponUsed = 1;
        }

        $validateData = new Varien_Object();
        $validateData
            ->setData(self::OPTION_DISCOUNT_AMOUNT,
                in_array('discount', $totalKeys) ? $totals['discount']->getValue() : 0)
            ->setData(self::OPTION_COUPON_USED, $couponUsed)
            ->setData(self::OPTION_COUPON_CODE, $object->getQuote()->getCouponCode())
            ->setData(self::OPTION_CURRENT_STORE, Mage::app()->getStore()->getId())
            ->setData(self::OPTION_CURRENCY_USED, Mage::app()->getStore()->getCurrentCurrencyCode())
        ;

        $value = $validateData->getData($this->getAttribute());
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
