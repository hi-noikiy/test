<?php
/**
 * file location:
 * app/code/Celigo/Magento2NetSuiteConnector/Service/ImportImageService.php
 */

namespace Celigo\Magento2NetSuiteConnector\Service;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportImageService
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * File interface
     *
     * @var File
     */
    private $file;

    /**
     * ProductRepository class
     *
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Processor class
     *
     * @var Processor
     */
    private $imageprocessor;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 101.0.0
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     * @since 101.0.0
     */
    private $mediaConfig;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 101.0.0
     */
    private $fileStorageDb;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Array class
     *
     * @var Array
     */
    private $defaultTypes = ['image', 'small_image', 'thumbnail', 'swatch_image'];

    /**
     * ImportImageService constructor
     *
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file,
        ProductRepository $productRepository,
        Processor $imageprocessor,
        FileSystem $filesystem,
        Config $mediaConfig,
        Database $fileStorageDb,
        ProductMetadataInterface $productMetadata
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->productRepository = $productRepository;
        $this->imageprocessor = $imageprocessor;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
        $this->fileStorageDb = $fileStorageDb;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $disabled
     *
     * @return bool
     */
    public function execute($product, $imageUrl, $fileName, $id = 0, $disabled = false, $imageType = [])
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        try {
            /** create folder if it is not exists */
            $this->file->checkAndCreateFolder($tmpDir);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'Due to improper permissions, we are unable to download the image. Error: ' . $e->getMessage()
                )
            );
        }
        /** @var string $newFileName */
        $newFileName = $tmpDir . $fileName;
        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($imageUrl, $newFileName);
        if ($result) {
            // load before update images
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

            // find existing image with file name
            $existingData = $this->findExistingImageData($existingMediaGalleryEntries, $fileName);

            /** add saved file to the $product gallery */
            try {
                if (!$existingData) {
                    if (version_compare($this->productMetadata->getVersion(), '2.2.2', '<')) {
                        $this->addImage($product, $newFileName, $imageType, true, $disabled);
                    } else {
                        $this->imageprocessor->addImage($product, $newFileName, $imageType, true, $disabled);
                    }
                } else {
                    foreach ($this->defaultTypes as $type) {
                        if (in_array($type, $imageType)) {
                            $product->setData($type, $existingData['file']); // updating types with new data
                        }
                    }
                    $existingData['disabled'] = $disabled; // override existing data with new data
                    $this->file->rm($newFileName); // remove temp image
                    $newFileName = $existingData['file']; // override existing data with new data
                    $this->imageprocessor->updateImage($product, $newFileName, $existingData);
                }
                $product->save();
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __(
                        'The SKU ' . $product->getSku() . ' unable to save image in Magento. ' . $e->getMessage()
                    )
                );
            }

            // load after update images
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

            if (!empty($id)) {
                try {
                    $this->unlinkExistingImage($existingMediaGalleryEntries, $id, $product);
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __(
                            'The SKU ' . $sku . ' unable to unlink existing image in Magento. Please retry.'
                        )
                    );
                }
            }

            // get new inserted Image Id
            $imageId = $this->getNewImageId($existingMediaGalleryEntries, $fileName);
        } else {
            throw new LocalizedException(__('The image Url must be valid. Unable to download Image: ' . $imageUrl));
        }

        return $imageId;
    }

    /**
     * @param array $existingMediaGalleryEntries
     * @param string $fileName
     * @return array $existingData
     */
    private function findExistingImageData($existingMediaGalleryEntries, $fileName)
    {
        $existingData = null;
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if (strpos($entry->getFile(), $fileName) !== false) {
                $existingData = $entry;
                break;
            }
        }
        return $existingData;
    }

    /**
     * @param array $existingMediaGalleryEntries
     * @param int $id
     * @param Product $product
     * @return void
     */
    private function unlinkExistingImage($existingMediaGalleryEntries, $id, $product)
    {
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if ($entry->getId() == $id) {
                unset($existingMediaGalleryEntries[$key]);
                break;
            }
        }
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        $this->productRepository->save($product);
    }

    /**
     * @param array $existingMediaGalleryEntries
     * @param string $fileName
     * @return int $imageId
     */
    private function getNewImageId($existingMediaGalleryEntries, $fileName)
    {
        $imageId = null;
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if (strpos($entry->getFile(), $fileName) !== false) {
                $imageId = $entry->getId();
                break;
            }
        }

        return $imageId;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    private function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'celigo/';
    }

    /**
     * Add image to media gallery and return new filename
     * We have added in this module because Magento 2.2.1 Image Processor addImage causing issue.
     * https://github.com/magento/magento2/issues/14407
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file file path of image in file system
     * @param string|string[] $mediaAttribute code of attribute with type 'media_image',
     *                                                      leave blank if image should be only in gallery
     * @param boolean $move if true, it will move source file
     * @param boolean $exclude mark image as disabled in product page view
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 101.0.0
     */
    private function addImage(
        \Magento\Catalog\Model\Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ) {
        $file = $this->mediaDirectory->getRelativePath($file);
        if (!$this->mediaDirectory->isFile($file)) {
            throw new LocalizedException(__('The image does not exist.'));
        }

        $pathinfo = pathinfo($file);
        $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
            throw new LocalizedException(__('Please correct the image file type.'));
        }

        $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
        $fileName = $dispretionPath . '/' . $fileName;

        $fileName = $this->getNotDuplicatedFilename($fileName, $dispretionPath);

        $destinationFile = $this->mediaConfig->getTmpMediaPath($fileName);

        try {
            /** @var $storageHelper \Magento\MediaStorage\Helper\File\Storage\Database */
            $storageHelper = $this->fileStorageDb;
            if ($move) {
                $this->mediaDirectory->renameFile($file, $destinationFile);

                //If this is used, filesystem should be configured properly
                $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
            } else {
                $this->mediaDirectory->copyFile($file, $destinationFile);

                $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('We couldn\'t move this file: %1.', $e->getMessage()));
        }

        $fileName = str_replace('\\', '/', $fileName);

        $attrCode = $this->imageprocessor->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position = 0;
        if (!is_array($mediaGalleryData)) {
            $mediaGalleryData = ['images' => []];
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if (isset($image['position']) && $image['position'] > $position) {
                $position = $image['position'];
            }
        }

        $position++;
        $mediaGalleryData['images'][] = [
            'file' => $fileName,
            'position' => $position,
            'label' => '',
            'media_type' => 'image',
            'disabled' => (int)$exclude,
        ];

        $product->setData($attrCode, $mediaGalleryData);

        if ($mediaAttribute !== null) {
            $this->imageprocessor->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }

    /**
     * Get filename which is not duplicated with other files in media temporary and media directories
     * We have added in this module because Magento 2.2.1 Image Processor addImage causing issue.
     * https://github.com/magento/magento2/issues/14407
     *
     * @param string $fileName
     * @param string $dispretionPath
     * @return string
     * @since 101.0.0
     */
    private function getNotDuplicatedFilename($fileName, $dispretionPath)
    {
        $fileMediaName = $dispretionPath . '/'
            . \Magento\MediaStorage\Model\File\Uploader::getNewFileName($this->mediaConfig->getMediaPath($fileName));
        $fileTmpMediaName = $dispretionPath . '/'
            . \Magento\MediaStorage\Model\File\Uploader::getNewFileName($this->mediaConfig->getTmpMediaPath($fileName));

        if ($fileMediaName != $fileTmpMediaName) {
            if ($fileMediaName != $fileName) {
                return $this->getNotDuplicatedFilename(
                    $fileMediaName,
                    $dispretionPath
                );
            } elseif ($fileTmpMediaName != $fileName) {
                return $this->getNotDuplicatedFilename(
                    $fileTmpMediaName,
                    $dispretionPath
                );
            }
        }

        return $fileMediaName;
    }
}
