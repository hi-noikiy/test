<?php

class TM_ProLabels_Model_Image_Uploader extends Mage_Core_Model_Abstract
{
    protected $_uploader;

    protected function _construct()
    {
        parent::_construct();
        if ($this->_uploader = @Mage::getModel('tmcore/image_uploader')) {
            $this->_uploader->setDirectory('prolabel');
        } else {
            throw new Exception(
                Mage::helper('tmcore')->__(
                    "We can't upload image. Update TM Core module."
                )
            );
        }
    }

    public function upload($object, $dataKey)
    {
        $this->_uploader->upload($object, $dataKey);
        return $this;
    }
}
