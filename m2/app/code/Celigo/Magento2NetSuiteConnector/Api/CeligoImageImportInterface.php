<?php
/**
 * file location:
 * app/code/Celigo/Magento2NetSuiteConnector/Api/CeligoImageImportInterface.php
 */

namespace Celigo\Magento2NetSuiteConnector\Api;

/**
 * Interface CeligoImageImportInterface
 * @api
 */
interface CeligoImageImportInterface
{
    
    /**
     * @api
     *
     * @param  Celigo\Magento2NetSuiteConnector\Api\Data\CeligoImageImportDataInterface $celigoImageData
     * @return string Image Id
     */
    public function importImage($entry);
}
