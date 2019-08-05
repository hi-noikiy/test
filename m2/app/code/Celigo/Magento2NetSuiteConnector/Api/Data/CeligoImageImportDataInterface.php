<?php
/**
 * file location:
 * app/code/Celigo/Magento2NetSuiteConnector/Api/Data/CeligoImageImportDataInterface.php
 */

namespace Celigo\Magento2NetSuiteConnector\Api\Data;

use \Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CeligoImageImportDataInterface
 * @api
 */
interface CeligoImageImportDataInterface extends ExtensibleDataInterface
{

    /**
     * image id
     */
    const ID = "id";
    /**
     * image types 'image', 'small_image', 'thumbnail', 'swatch_image'
     */
    const TYPES = "types";
    const IMAGE_URL = "image_url";
    const FILE_NAME = "file_name";
    const SKU = "sku";

    /**
     * @return int
     */
    public function getId();

    /**
     * @param $id int
     * @return void
     */
    public function setId($id);

    /**
     * @return string[]
     */
    public function getTypes();

    /**
     * @param $types string[]
     * @return void
     */
    public function setTypes($types);

    /**
     * @return string
     */
    public function getImageUrl();

    /**
     * @param $imageUrl string
     * @return void
     */
    public function setImageUrl($imageUrl);

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @param $fileName string
     * @return void
     */
    public function setFileName($fileName);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param $sku string
     * @return void
     */
    public function setSku($sku);
}
