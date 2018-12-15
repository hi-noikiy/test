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



class Mirasvit_Rma_Helper_Attachment extends Mage_Core_Helper_Abstract
{

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return int
     */
    public function getAllowedSize()
    {
        return $this->getConfig()->getGeneralFileSizeLimit()  * 1024 * 1024;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->getConfig()->getGeneralFileAllowedExtensions();
    }

    /**
     * @return string
     */
    public function getAttachmentLimits()
    {
        $message = array();
        $allowedExtensions = $this->getAllowedExtensions();
        if (count($allowedExtensions)) {
            $message[] = $this->__('Allowed extensions:').' '.implode(', ', $allowedExtensions);
        }
        if ($allowedSize = $this->getAllowedSize()) {
            $message[] = $this->__('Maximum size:').' '.$allowedSize.'Mb';
        }

        return implode('<br>', $message);
    }

    /**
     * @param Mirasvit_Rma_Model_Comment $comment
     * @param Varien_Object $email
     * @return void
     */
    public function saveAttachments($comment, $email = false)
    {
        $attachments = array();
        if ($email) {
            $attachments = $email->getAttachments();
        } else if (isset($_FILES['attachment']['name'][0]) && $_FILES['attachment']['name'][0] != '') {
            $i = 0;
            foreach ($_FILES['attachment']['name'] as $name) {
                if ($name == '') {
                    continue;
                }

                $attached = new Varien_Object();
                $attached->setData('name', $name);
                $attached->setData('type', $_FILES['attachment']['type'][$i]);
                $attached->setData('size', $_FILES['attachment']['size'][$i]);

                $tempName = $_FILES['attachment']['tmp_name'][$i];
                $body = @file_get_contents(addslashes($tempName));
                $attached->setData('body', $body);
                $attachments[] = $attached;
                $i++;
            }
        }

        foreach ($attachments as $attachment) {
            $ext = pathinfo($attachment->getName(), PATHINFO_EXTENSION);

            $allowedFileExtensions = $this->getConfig()->getGeneralFileAllowedExtensions();
            if (count($allowedFileExtensions) && !in_array($ext, $allowedFileExtensions)) {
                continue;
            }

            $sizeLimit = $this->getAllowedSize();
            if ($sizeLimit && $attachment->getSize() > $sizeLimit) {
                continue;
            }

            $record = Mage::getModel('rma/attachment')
                ->setCommentId($comment->getId())
                ->setExternalId()
                ->setName($attachment->getName())
                ->setSize($attachment->getSize())
                ->setBody($attachment->getBody())
                ->setType($attachment->getType());
            if ($email) {
                $record->setEmailId($email->getId());
            }
            $record->save();
        }
    }
}
