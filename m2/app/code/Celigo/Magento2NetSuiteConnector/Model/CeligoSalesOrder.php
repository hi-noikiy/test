<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Model;

use Magento\Framework\Api\AttributeValueFactory;
use \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface;
use \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderInterface;
use \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface;

/**
 * Gift CeligoSalesOrder model
 *
 * @method \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder _getResource()
 * @method \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder getResource()
 *
 */
class CeligoSalesOrder extends \Magento\Framework\Model\AbstractExtensibleModel implements CeligoSalesOrderInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder');
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(self::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsExportedToIo()
    {
        return $this->getData(self::IS_EXPORTED_TO_IO);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsExportedToIo($value)
    {
        return $this->setData(self::IS_EXPORTED_TO_IO, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(CeligoSalesOrderExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
