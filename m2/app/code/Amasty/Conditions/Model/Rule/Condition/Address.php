<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conditions
 */


namespace Amasty\Conditions\Model\Rule\Condition;

use Amasty\Conditions\Model\Constants;

class Address extends \Magento\Rule\Model\Condition\AbstractCondition
{
    const CUSTOM_OPERATORS = [
        'shipping_address_line',
        'city',
    ];

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $country;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Payment\Model\Config\Source\Allmethods
     */
    private $allMethods;

    /**
     * @var \Amasty\Conditions\Model\Address
     */
    private $address;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Payment\Model\Config\Source\Allmethods $allMethods,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Amasty\Conditions\Model\Address $address,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productMetadata = $productMetadata;
        $this->country = $country;
        $this->allMethods = $allMethods;
        $this->address = $address;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'billing_country' => __('Billing Address Country'),
            'payment_method' => __('Payment Method'),
            'shipping_address_line' => __('Shipping Address Line'),
            'city' => __('City'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOperatorSelectOptions()
    {
        if (in_array($this->getAttribute(), self::CUSTOM_OPERATORS)) {
            $operators = $this->getOperators();
            $type = $this->getInputType();
            $result = [];
            $operatorByType = $this->getOperatorByInputType();
            foreach ($operators as $operatorKey => $operatorValue) {
                if (!$operatorByType || in_array($operatorKey, $operatorByType[$type])) {
                    $result[] = ['value' => $operatorKey, 'label' => $operatorValue];
                }
            }

            return $result;
        }

        return parent::getOperatorSelectOptions();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOperators()
    {
        if ($this->getAttribute() === 'shipping_address_line') {
            return [
                '{}' => __('contains'),
                '!{}' => __('does not contain'),
            ];
        } elseif ($this->getAttribute() === 'city') {
            return [
                '{}' => __('contains'),
                '!{}' => __('does not contain'),
                '==' => __('is'),
                '!=' => __('is not'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ];
        }

        return [];
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInputType()
    {
        return $this->getAttribute() === 'shipping_address_line' || $this->getAttribute() ==='city'
            ? 'string'
            : 'select';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueElementType()
    {
        return $this->getAttribute() === 'shipping_address_line' || $this->getAttribute() === 'city'
            ? 'text'
            : 'select';
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData(Constants::VALUE_SELECT_OPTIONS)) {
            switch ($this->getAttribute()) {
                case 'billing_country':
                    $options = $this->country->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->allMethods->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData(Constants::VALUE_SELECT_OPTIONS, $options);
        }

        return $this->getData(Constants::VALUE_SELECT_OPTIONS);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            $address = $address->getQuote()->isVirtual()
                ? $address->getQuote()->getBillingAddress()
                : $address->getQuote()->getShippingAddress();
        }

        $attrValue = $this->getAttributeValue($address);
        if (!$attrValue) {
            $attrValue = $this->getDefaultAttrValue($address);
        }

        return parent::validateAttribute($attrValue);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultAttrValue(\Magento\Quote\Model\Quote\Address $address)
    {
        $attrValue = null;
        switch ($this->getAttribute()) {
            case 'payment_method':
                $attrValue = $address->getPaymentMethod();
                break;

            case 'shipping_address_line':
                $attrValue = $address->getStreetFull();
                break;

            case 'billing_country':
                $attrValue = $address->getCountryId();
                break;

            case 'city':
                $attrValue = $address->getCity();
                break;
        }

        return $attrValue;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return int|mixed|null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeValue(\Magento\Quote\Model\Quote\Address $address)
    {
        $attrValue = null;
        if ($this->address->isAdvancedConditions($address)) {
            $advConditions = $address->getExtensionAttributes()->getAdvancedConditions();
            switch ($this->getAttribute()) {
                case 'billing_country':
                    $attrValue = $advConditions->getSameAsBilling()
                        ? $address->getQuote()->getShippingAddress()->getCountryId()
                        : $address->getCountryId();
                    break;

                case 'payment_method':
                    $attrValue = $advConditions->getPaymentMethod();
                    break;

                case 'shipping_address_line':
                    $attrValue = $this->getStreetFull($advConditions->getAddressLine());
                    break;

                case 'city':
                    $attrValue = $advConditions->getCity();
                    break;
            }
        }

        return $attrValue;
    }

    /**
     * @param $address
     * @return mixed|string
     */
    private function getStreetFull($address)
    {
        return is_array($address) ? implode("\n", $address) : $address;
    }
}
