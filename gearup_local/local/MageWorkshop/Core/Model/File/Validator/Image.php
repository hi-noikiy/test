<?php

/**
 * Class is full copy of Mage_Core_Model_File_Validator_Image,
 * it was added for compatibility with Magento less than 1.9.2.3 version
 *
 * Validator for check is uploaded file is image
 */
class MageWorkshop_Core_Model_File_Validator_Image
{
    const NAME = "isImage";
    
    protected $_allowedImageTypes = array(
        IMAGETYPE_JPEG,
        IMAGETYPE_GIF,
        IMAGETYPE_JPEG2000,
        IMAGETYPE_PNG,
        IMAGETYPE_ICO,
        IMAGETYPE_TIFF_II,
        IMAGETYPE_TIFF_MM
    );
    
    /**
     * Setter for allowed image types
     *
     * @param array $imageFileExtensions
     * @return $this
     */
    public function setAllowedImageTypes(array $imageFileExtensions = array())
    {
        $map = array(
            'tif' => array(IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM),
            'tiff' => array(IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM),
            'jpg' => array(IMAGETYPE_JPEG, IMAGETYPE_JPEG2000),
            'jpe' => array(IMAGETYPE_JPEG, IMAGETYPE_JPEG2000),
            'jpeg' => array(IMAGETYPE_JPEG, IMAGETYPE_JPEG2000),
            'gif' => array(IMAGETYPE_GIF),
            'png' => array(IMAGETYPE_PNG),
            'ico' => array(IMAGETYPE_ICO),
            'apng' => array(IMAGETYPE_PNG)
        );
        
        $this->_allowedImageTypes = array();
        
        foreach ($imageFileExtensions as $extension) {
            if (isset($map[$extension])) {
                foreach ($map[$extension] as $imageType) {
                    $this->_allowedImageTypes[$imageType] = $imageType;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Validation callback for checking is file is image
     *
     * @param  string $filePath Path to temporary uploaded file
     * @return null
     * @throws Mage_Core_Exception
     */
    public function validate($filePath)
    {
        $fileInfo = getimagesize($filePath);
        if (is_array($fileInfo) and isset($fileInfo[2])) {
            if ($this->isImageType($fileInfo[2])) {
                return null;
            }
        }
        throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid MIME type.'));
    }
    
    /**
     * Returns is image by image type
     * @param int $nImageType
     * @return bool
     */
    protected function isImageType($nImageType)
    {
        return in_array($nImageType, $this->_allowedImageTypes);
    }
    
}
