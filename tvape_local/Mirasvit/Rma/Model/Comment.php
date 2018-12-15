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



/**
 * @method Mirasvit_Rma_Model_Resource_Comment_Collection|Mirasvit_Rma_Model_Comment[] getCollection()
 * @method Mirasvit_Rma_Model_Comment load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Comment setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Comment setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Comment getResource()
 * @method int getStatusId()
 * @method Mirasvit_Rma_Model_Comment setStatusId(int $statusId)
 * @method int getUserId()
 * @method Mirasvit_Rma_Model_Comment setUserId(int $userId)
 * @method int getCustomerId()
 * @method Mirasvit_Rma_Model_Comment setCustomerId(int $entityId)
 * @method string getCustomerName()
 * @method $this setCustomerName(string $param)
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $param)
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $param)
 * @method string getText()
 */
class Mirasvit_Rma_Model_Comment extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/comment');
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
     * @var Mirasvit_Rma_Model_Status
     */
    protected $_status = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Status
     */
    public function getStatus()
    {
        if (!$this->getStatusId()) {
            return false;
        }
        if ($this->_status === null) {
            $this->_status = Mage::getModel('rma/status')->load($this->getStatusId());
        }

        return $this->_status;
    }

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_user = null;

    /**
     * @return bool|Mirasvit_Rma_Model_User
     */
    public function getUser()
    {
        if (!$this->getUserId()) {
            return false;
        }
        if ($this->_user === null) {
            $this->_user = Mage::getModel('admin/user')->load($this->getUserId());
        }

        return $this->_user;
    }

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->getCustomerId()) {
            return false;
        }
        if ($this->_customer === null) {
            $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        }

        return $this->_customer;
    }

    /************************/

    /**
     * @param string $text
     * @param bool $isHtml
     * @return Mirasvit_Rma_Model_Comment
     */
    public function setText($text, $isHtml)
    {
        $this->setIsHtml($isHtml);
        if (!$isHtml) {
            $text = strip_tags($text);
        }
        $this->setData('text', $text);

        return $this;
    }

    /**
     * @return string
     */
    public function getTextHtml()
    {
        if ($this->getIsHtml()) {
            return $this->getText();
        } else {
            return Mage::helper('rma')->convertToHtml($this->getText());
        }
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        $attachments = array();

        $attaches = Mage::getModel('rma/attachment')->getCollection()
            ->addFieldToFilter('comment_id', $this->getId());

        foreach ($attaches as $attachment) {
            $attachments[] = $attachment;
        }

        if ($oldAttach = Mage::helper('mstcore/attachment')->getAttachments('COMMENT', $this->getId())) {
            foreach ($oldAttach as $attachment) {
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }

    /**
     * @return int
     */
    public function getTriggeredBy()
    {
        if ($this->getUser()) {
            return Mirasvit_Rma_Model_Config::USER;
        } elseif ($this->getCustomer()) {
            return Mirasvit_Rma_Model_Config::CUSTOMER;
        } elseif ($this->getCustomerName()) {
            return Mirasvit_Rma_Model_Config::CUSTOMER; //guest
        } else {
            // automated reply also should count as staff
            return Mirasvit_Rma_Model_Config::USER;
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        if ($this->getIsVisibleInFrontend()) {
            return Mirasvit_Rma_Model_Config::COMMENT_PUBLIC;
        }

        return Mirasvit_Rma_Model_Config::COMMENT_INTERNAL;
    }
}
