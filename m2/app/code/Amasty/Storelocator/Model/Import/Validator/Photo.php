<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import\Validator;

use \Amasty\Storelocator\Model\Import\Location as Location;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Photo extends AbstractImportValidator implements RowValidatorInterface
{

    const URL_REGEXP = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';

    const PATH_REGEXP = '#^(?!.*[\\/]\.{2}[\\/])(?!\.{2}[\\/])[-\w.\\/]+$#';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * @param string $string
     * @return bool
     */
    public function checkValidUrl($string)
    {
        return preg_match(self::URL_REGEXP, $string);
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function checkPath($string)
    {
        return preg_match(self::PATH_REGEXP, $string);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function checkFileExists($path)
    {
        return file_exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * @param string $imageDir
     * @return string
     */
    private function prepareImageDir($imageDir)
    {
        if ($imageDir[0] !== '/') {
            $imageDir = '/' . $imageDir;
        }
        if (substr($imageDir, -1) !== '/') {
            $imageDir = $imageDir . '/';
        }

        return $imageDir;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $valid = $this->validateImages($value);
        if (!$valid) {
            $this->_addMessages([self::ERROR_INVALID_PHOTO]);
        }

        return $valid;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return bool
     */
    public function validateImages($value)
    {
        $this->_clearMessages();
        $valid = true;
        $fileNames = [];
        if (isset($value[Location::COL_LOCATION_GALLERY]) && !empty($value[Location::COL_LOCATION_GALLERY])) {
            $fileNames = explode(',', $value[Location::COL_LOCATION_GALLERY]);
        }
        if (isset($value[Location::COL_MARKER]) && !empty($value[Location::COL_MARKER])) {
            array_push($fileNames, $value[Location::COL_MARKER]);
        }
        if ($fileNames) {
            $valid = $this->validatePaths($fileNames);
        }

        return $valid;
    }

    /**
     * Validate paths
     *
     * @param array $paths
     * @return bool
     */
    public function validatePaths($paths)
    {
        $importParams = $this->context->getParameters();
        $rootFolder = $this->directoryList->getPath(DirectoryList::ROOT);
        $importPath = $importParams[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR];
        if (!empty($importPath)) {
            $importPath = $this->prepareImageDir($importPath);
        }
        $valid = true;
        foreach ($paths as $path) {
            $imageFullPath = $rootFolder . $importPath . $path;
            if ($this->checkPath($imageFullPath)) {
                if (!$this->checkFileExists($imageFullPath)) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }
}
