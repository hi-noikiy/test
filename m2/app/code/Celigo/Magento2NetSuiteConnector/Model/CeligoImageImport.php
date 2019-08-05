<?php
/**
 * file location:
 * app/code/Celigo/Magento2NetSuiteConnector/Model/CeligoImageImport.php
 */

namespace Celigo\Magento2NetSuiteConnector\Model;

use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\Exception\LocalizedException;
use \Celigo\Magento2NetSuiteConnector\Service\ImportImageService;

/**
 * Celigo sales order repository object.
 */
class CeligoImageImport implements \Celigo\Magento2NetSuiteConnector\Api\CeligoImageImportInterface
{

    /**
     * ProductRepository interface
     *
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ImportImageService
     *
     * @var ImportImageService
     */
    private $imageImportService;
        
    public function __construct(
        ProductRepository $productRepository,
        ImportImageService $imageImportService
    ) {
        $this->productRepository = $productRepository;
        $this->imageImportService = $imageImportService;
    }
    
    /**
     * {@inheritDoc}
     */
    public function importImage($entry)
    {
        $imageId = $entry->getId();
        $types = $entry->getTypes();
        $imageUrl = $entry->getImageUrl();

        // validating Image Url
        if (empty($imageUrl)) {
            throw new LocalizedException(__('The image Url should not be empty.'));
        }

        // validating File Name
        $fileName = $entry->getFileName();
        if (empty($fileName)) {
            throw new LocalizedException(__('The file name should not be empty.'));
        }

        // validating Sku
        $sku = $entry->getSku();
        if (empty($sku)) {
            throw new LocalizedException(__('The sku should not be empty.'));
        }

        // initializing Product using SKU
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'The SKU ' . $sku . ' does not exist in Magento. Please retry after exporting the SKU in Magento.'
                )
            );
        }

        $result = $this->imageImportService->execute($product, $imageUrl, $fileName, $imageId, false, $types);
        return $result;
    }
}
