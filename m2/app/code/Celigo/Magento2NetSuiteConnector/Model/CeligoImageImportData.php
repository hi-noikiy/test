<?php
/**
 * file location:
 * app/code/Celigo/Magento2NetSuiteConnector/Model/CeligoImageImportData.php
 */

namespace Celigo\Magento2NetSuiteConnector\Model;

use Magento\Framework\Api\AttributeValueFactory;
use \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoImageImportDataInterface;
use \Celigo\Magento2NetSuiteConnector\Api\CeligoImageImportInterface;
use \Magento\Framework\Model\AbstractExtensibleModel;

/**
 * CeligoImageImportData model
 */
class CeligoImageImportData extends AbstractExtensibleModel implements CeligoImageImportDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->getData(self::TYPES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTypes($types)
    {
        return $this->setData(self::TYPES, $types);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl()
    {
        return $this->getData(self::IMAGE_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->getData(self::FILE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }
}
