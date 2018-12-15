<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Attachment extends Mage_Core_Model_Abstract
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/attachment');
    }

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return string
     */
    public function getGlobalStorage()
    {
        return $this->getConfig()->getGeneralFileStorage();
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }


    /**
     * @return string
     */
    public function getAttachmentFolderPath()
    {
        return Mage::getBaseDir('media').'/rma/attachments/';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $store = Mage::app()->getStore();
        return Mage::getUrl('rma/rma_attachment/download', array('id' => $this->getExternalId(),
            '_store' => $store->getId()));
    }

    /**
     * Get attachment id.
     * If id is empty, we generate it.
     *
     * @return string
     */
    public function getExternalId()
    {
        if (!$this->getData('external_id')) {
            $this->setExternalId();
        }

        return $this->getData('external_id');
    }

    /**
     * Returns attachment path in the filesystem.
     *
     * @return string
     */
    public function getExternalPath()
    {
        $hashCode = $this->getExternalId();

        return $this->getAttachmentFolderPath().substr($hashCode, 0, 1).DS.substr($hashCode, 1, 2).DS.
            $this->getExternalId();
    }

    /**
     * Depending of storage type, recovers body of attachment either from database or from file system.
     * @return string
     */
    public function getBody()
    {
        if ($this->getStorage() == Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_FS) {
            return file_get_contents($this->getExternalPath());
        }

        return $this->getData('body');
    }

    /**
     * Depending of storage type, stores body of attachment either in database or in file system.
     * @param string $decodedContent
     * @return Mirasvit_Rma_Model_Attachment
     */
    public function setBody($decodedContent)
    {
        if (($this->getGlobalStorage() == Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_FS)) {
            try {
                if (!file_exists(dirname($this->getExternalPath()))) {
                    mkdir(dirname($this->getExternalPath()), 0777, true);
                }
                $attachFile = fopen($this->getExternalPath(), 'w');
                fwrite($attachFile, $decodedContent);
                fclose($attachFile);
            } catch (Exception $e) {
                Mage::throwException("Can't write to {$this->getAttachmentFolderPath()}. Please, check that folder" .
                " exists and webserver/cron has permissions to write into this folder.");
            }
            $this->setStorage(Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_FS);
        } else {
            $this->setData('body', $decodedContent);
            $this->setStorage(Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_DATABASE);
        }
        $this->save();

        return $this;
    }

    /**
     * @param string $attachmentName
     * @return Mirasvit_Rma_Model_Attachment
     */
    public function setName($attachmentName)
    {
        /*
         * Here we check for encoded name (when national or other extended characters used).
         * Examples:
         * =?ISO-8859-5?B?t9Dh0NTYIN3e0t7T3iDh4+Hf9tvs3d7T3iDU3tPe0t7g4y5kb2N4?=  (base64-encoded)
         * =?utf-8?Q?Sluz=CC=8Cobna=CC=81-cesta=2Edocx?=  (quoted-encoded)
         */
        if (strpos($attachmentName, '?=')) {

            // Break name into three component: =?ISO-8859-5?B?t9Dh0NTYIN3e0t7T3iDh4+Hf9tvs3d7T3iDU3tPe0t7g4y5kb2N4?=
            // charset: ISO-8859-5,
            // encoding: B
            // name: t9Dh0NTYIN3e0t7T3iDh4+Hf9tvs3d7T3iDU3tPe0t7g4y5kb2N4
            $parts = explode('?', $attachmentName);

            // Check whether encoding is valid and supported by iConv
            if (isset($parts[1]) && in_array($parts[1], mb_list_encodings())) {
                switch ($parts[2]) {
                    case 'B': // base64 encoded (here is first example decoded)
                        $attachmentName = base64_decode($parts[3]);
                        break;
                    case 'Q': //quoted printable encoded (here is second example decoded)
                        $attachmentName = quoted_printable_decode($parts[3]);
                        break;
                }
                // Convert decoded name using proper encoding
                $attachmentName = iconv($parts[1], 'UTF-8', $attachmentName);
            }
        }

        // Set data
        $this->setData('name', $attachmentName);

        return $this;
    }

    /**
     * @return Mirasvit_Rma_Model_Attachment
     */
    public function setExternalId()
    {
        $id = md5(time().Mage::helper('mstcore/string')->generateRandNum(10));
        $this->setData('external_id', $id);
        return $this;
    }

}
