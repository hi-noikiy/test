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



class Mirasvit_Rewards_Model_Earning_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('rewards/earning_rule_condition_combine');
    }

    /**
     * Returns all available Conditions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        if ($this->getRule()->getType()) {
            $type = $this->getRule()->getType();
        } else {
            $type = Mage::app()->getRequest()->getParam('rule_type');
        }

        if ($type == Mirasvit_Rewards_Model_Earning_Rule::TYPE_CART) {
            $all = $this->_getCartConditions();
            // Addendum to add special cart attributes
            $cartAttributes = $this->_getSpecialCartAttributes();
            $attributes = $this->convertToAttributes($cartAttributes, 'cart', 'value');
            $attributes['label'] = 'Additional Cart Attributes';
            $all[] = $attributes;

            // Addendum to add customer attributes
            $itemAttributes = $this->_getCustomerAttributes();
            $attributes = $this->convertToAttributes($itemAttributes, 'customer', 'value');
            $attributes['label'] = 'Customer';
            $all[] = $attributes;

            return $all;
        } elseif ($type == Mirasvit_Rewards_Model_Earning_Rule::TYPE_BEHAVIOR) {
            $itemAttributes = $this->_getCustomerAttributes();
            $attributes = $this->convertToAttributes($itemAttributes, 'customer', 'Customer');
            $itemAttributes = $this->_getReferredCustomerAttributes();
            $referredAttributes = $this->convertToAttributes($itemAttributes, 'referred_customer', 'Referred Customer');
            $attributes = array_merge_recursive($attributes, $referredAttributes);
        } else {
            $itemAttributes = $this->_getProductAttributes();
            $attributes = $this->convertToAttributes($itemAttributes, 'product', 'Product Attributes');

            // Add to product rules some attributes from cart
            $cartAttributes = $this->_getSpecialCartAttributes();
            unset($cartAttributes['coupon_used'],
                $cartAttributes['coupon_code'],
                $cartAttributes['discount_amount']);

            $specials = $this->convertToAttributes($cartAttributes, 'cart', 'Additional Conditions');
            $attributes = array_merge_recursive($attributes, $specials);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value' => 'rewards/earning_rule_condition_combine%'.$type,
                'label' => Mage::helper('rewards')->__('Conditions Combination'),
            ),
        ));

        foreach ($attributes as $group => $arrAttributes) {
            $conditions = array_merge_recursive($conditions, array(
                array(
                    'label' => $group,
                    'value' => $arrAttributes,
                ),
            ));
        }

        return $conditions;
    }

    /**
     * Converts attributes array to displayable form
     *
     * @param array $itemAttributes - item attributes
     * @param string $condition - condition class
     * @param string $group - condition group title
     * @return array
     */
    protected function convertToAttributes($itemAttributes, $condition, $group)
    {
        $attributes = array();
        foreach ($itemAttributes as $code => $label) {
            $attributes[$group][] = array(
                'value' => 'rewards/earning_rule_condition_'.$condition.'|'.$code,
                'label' => $label,
            );
        }

        return $attributes;
    }

    /**
     * Filters product attributes
     *
     * @param array $productCollection - all product attributes
     * @return Mirasvit_Rewards_Model_Earning_Rule_Condition_Combine
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * Returns product attributes
     *
     * @return array
     */
    protected function _getProductAttributes()
    {
        $productCondition = Mage::getModel('rewards/earning_rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();

        return $productAttributes;
    }

    /**
     * Returns cart conditions
     *
     * @return array
     */
    protected function _getCartConditions()
    {
        $addressCondition = Mage::getModel('salesrule/rule_condition_address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = array('value' => 'salesrule/rule_condition_address|'.$code, 'label' => $label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'salesrule/rule_condition_product_found',
                'label' => Mage::helper('salesrule')->__('Product attribute combination')),
            array('value' => 'salesrule/rule_condition_product_subselect',
                'label' => Mage::helper('salesrule')->__('Products subselection')),
            array('value' => 'salesrule/rule_condition_combine',
                'label' => Mage::helper('salesrule')->__('Conditions combination')),
            array('label' => Mage::helper('salesrule')->__('Cart Attribute'), 'value' => $attributes),
        ));

        $additional = new Varien_Object();
        Mage::dispatchEvent('salesrule_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    /**
     * Returns special cart attributes
     *
     * @return array
     */
    protected function _getSpecialCartAttributes()
    {
        $cartCondition = Mage::getModel('rewards/earning_rule_condition_cart');
        $cartAttributes = $cartCondition->loadAttributeOptions()->getAttributeOption();

        return $cartAttributes;
    }

    /**
     * Returns customer attributes
     *
     * @return array
     */
    protected function _getCustomerAttributes()
    {
        $customerCondition = Mage::getModel('rewards/earning_rule_condition_customer');
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();

        return $customerAttributes;
    }

    /**
     * Returns attributes of a Referred Customer
     *
     * @return array
     */
    protected function _getReferredCustomerAttributes()
    {
        $customerCondition = Mage::getModel('rewards/earning_rule_condition_referred_customer');
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();

        return $customerAttributes;
    }
}
