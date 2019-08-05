<?php
/**
 * Created by PhpStorm.
 * User: Celigo Developer
 * Date: 6/30/2016
 * Time: 1:58 PM
 */
namespace Celigo\Magento2NetSuiteConnector\Api\Data;

use \Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CeligoSalesOrderInterface
 * @api
 */
interface CeligoSalesOrderInterface extends ExtensibleDataInterface
{
    const PARENT_ID = "parent_id";
    const IS_EXPORTED_TO_IO = "is_exported_to_io";
  
    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param $id int
     * @return void
     */
    public function setParentId($id);

    /**
     * @return int
     */
    public function getIsExportedToIo();
    /**
     * @param $value int
     * @return void
     */
    public function setIsExportedToIo($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderExtensionInterface $extensionAttributes
    );
}
