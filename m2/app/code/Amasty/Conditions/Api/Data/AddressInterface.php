<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conditions
 */


namespace Amasty\Conditions\Api\Data;

interface AddressInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const PAYMENT_METHOD = 'payment_method';
    const ADDRESS_LINE = 'shipping_address_line';
    const SAME_AS_BILLING = 'same_as_billing';
    const CUSTOM_ATTRIBUTES = 'custom_attributes';
    const CITY = 'city';

    /**#@-*/

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get address line
     *
     * @return string[]
     */
    public function getAddressLine();

    /**
     * @param $addressLine
     *
     * @return $this
     */
    public function setAddressLine($addressLine);

    /**
     * Get same as billing checkbox
     *
     * @return string
     */
    public function getSameAsBilling();

    /**
     * @param $sameAsBilling
     *
     * @return $this
     */
    public function setSameAsBilling($sameAsBilling);

    /**
     * Get custom attributes
     *
     * @return string
     */
    public function getCustomAttributes();

    /**
     * @param $customAttributes
     *
     * @return $this
     */
    public function setCustomAttributes($customAttributes);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * @param $city
     *
     * @return $this
     */
    public function setCity($city);
}
