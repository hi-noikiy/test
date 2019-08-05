<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conditions
 */


namespace Amasty\Conditions\Model;

use Amasty\Conditions\Api\Data\AddressInterface;
use Magento\Framework\DataObject;

class Address extends DataObject implements AddressInterface
{
    /**
     * @param $model
     * @return bool
     */
    public function isAdvancedConditions($model)
    {
        return is_object($model->getExtensionAttributes())
            && $model->getExtensionAttributes()->getAdvancedConditions();
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->_getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine()
    {
        return $this->_getData(self::ADDRESS_LINE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressLine($addressLine)
    {
        return $this->setData(self::ADDRESS_LINE, $addressLine);
    }

    /**
     * {@inheritdoc}
     */
    public function getSameAsBilling()
    {
        return $this->_getData(self::SAME_AS_BILLING);
    }

    /**
     * {@inheritdoc}
     */
    public function setSameAsBilling($sameAsBilling)
    {
        return $this->setData(self::SAME_AS_BILLING, $sameAsBilling);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributes()
    {
        return $this->_getData(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes($customAttributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $customAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }
}
