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
 * @method Mirasvit_Rma_Model_Resource_Rma_Collection|Mirasvit_Rma_Model_Rma[] getCollection()
 * @method Mirasvit_Rma_Model_Rma load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Rma setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Rma setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Rma getResource()
 * @method int getRmaId()
 * @method Mirasvit_Rma_Model_Rma setRmaId(int $rmaId)
 * @method int getOrderId()
 * @method Mirasvit_Rma_Model_Rma setOrderId(int $entityId)
 * @method int getStoreId()
 * @method Mirasvit_Rma_Model_Rma setStoreId(int $storeId)
 * @method int getCustomerId()
 * @method Mirasvit_Rma_Model_Rma setCustomerId(int $entityId)
 * @method int getUserId()
 * @method Mirasvit_Rma_Model_Rma setUserId(int $entityId)
 * @method int getStatusId()
 * @method Mirasvit_Rma_Model_Rma setStatusId(int $statusId)
 * @method bool getIsArchived()
 * @method Mirasvit_Rma_Model_Rma setIsArchived(bool $flag)
 * @method string getLastReplyName()
 * @method Mirasvit_Rma_Model_Rma setLastReplyName(string $field)
 * @method bool getIsAdminRead()
 * @method Mirasvit_Rma_Model_Rma setIsAdminRead(bool $field)
 * @method string getCreatedAt()
 * @method Mirasvit_Rma_Model_Rma setCreatedAt(string $field)
 * @method string getUpdatedAt()
 * @method Mirasvit_Rma_Model_Rma setUpdatedAt(string $field)
 * @method array getExchangeOrderIds()
 * @method Mirasvit_Rma_Model_Rma setExchangeOrderIds(array $ids)
 * @method array getCreditMemoIds()
 * @method Mirasvit_Rma_Model_Rma setCreditMemoIds(array $ids)
 * @method string getGuestId()
 * @method $this setGuestId(string $param)
 * @method string getEmail()
 * @method $this setEmail(string $param)
 */
class Mirasvit_Rma_Model_Rma extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/rma');
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
     * Items Collection
     */
    protected $_itemCollection;

    /**
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            $this->_itemCollection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId())
                ->addFieldToFilter('is_removed', 0)
                ->addFieldToFilter('qty_requested', array('gt' => 0))
                ->addOrder('main_table.item_id', 'ASC')
            ;
        }

        return $this->_itemCollection;
    }

    /**
     * Offline Items Collection
     */
    protected $_offlineItemCollection;

    /**
     * @return Mirasvit_Rma_Model_Offline_Item[]|Mirasvit_Rma_Model_Resource_Offline_Item_Collection
     */
    public function getOfflineItemCollection()
    {
        if (!$this->_offlineItemCollection) {
            $this->_offlineItemCollection = Mage::getModel('rma/offline_item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId())
            ;
        }

        return $this->_offlineItemCollection;
    }

    /**
     * Items Collection
     */
    protected $itemsCollection;

    /**
     *
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getItemsCollection()
    {
        if (!$this->itemsCollection || !count($this->itemsCollection)) {
            $this->itemsCollection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId())
                ->addFieldToFilter('is_removed', 0);
        }

        return $this->itemsCollection;
    }

    /**
     * Comments Collection
     */
    protected $_commentCollection;

    /**
     * @return Mirasvit_Rma_Model_Comment[]|Mirasvit_Rma_Model_Resource_Comment_Collection
     */
    public function getCommentCollection()
    {
        if (!$this->_commentCollection) {
            $this->_commentCollection = Mage::getModel('rma/comment')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId());
        }

        return $this->_commentCollection;
    }


    /**
     * @var array
     */
    protected $orders = null;

    /**
     * @return Mage_Sales_Model_Order[]
     */
    public function getOrders()
    {
        if ($this->orders == null || !count($this->orders)) {
            $orderIds = array(0);
            foreach ($this->getItemsCollection() as $item) {
                $orderIds[] = $item->getOrderId();
            }
            $this->orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('entity_id', $orderIds);
        }

        return $this->orders;
    }

    /**
     * @var array
     */
    protected $offlineOrders = null;

    /**
     * @return Mirasvit_Rma_Model_Offline_Order[]
     */
    public function getOfflineOrders()
    {
        if ($this->offlineOrders == null) {
            $orderIds = array(0);
            foreach ($this->getOfflineItemCollection() as $item) {
                $orderIds[] = $item->getOfflineOrderId();
            }
            $this->offlineOrders = Mage::getModel('rma/offline_order')->getCollection()
                ->addFieldToFilter('offline_order_id', $orderIds);
        }

        return $this->offlineOrders;
    }

    /**
     * @param int $index
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($index = 0)
    {
        $orders = array_values($this->getOrders()->getItems());
        if (count($orders) <= $index) {
            return new Varien_Object();
        }
        $order = $orders[$index];
        return ($order->getId()) ? $order : new Varien_Object();
    }

    /**
     * @return array
     */
    public function getOrdersId()
    {
        // @codingStandardsIgnoreStart - SQL fields do not fit with coding standards
        if (is_string($this->orders_id)) {
            return explode(',', $this->orders_id);
        }

        return (array) $this->orders_id;
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param int        $id
     * @param int|string $customerEmail
     *
     * @return bool
     */
    public function getIsRmaAllowed($id, $customerEmail)
    {
        $customerId = (int) $customerEmail;

        // guest customer
        if ($customerId != $customerEmail) {
            $customerId = null;
        }

        $orders = Mage::helper('rma')
            ->getAllowedOrderCollection($customerId, false);

        if (!$customerId) {
            $orders->addFieldToFilter('customer_email', $customerEmail);
        }

        $ordersId = array();
        if (!$orders->count()) {
            return false;
        }
        foreach ($orders as $order) {
            $ordersId[$order->getId()] = $order->getId();
        }

        $collection = Mage::helper('rma')
            ->getRmaByOrder($ordersId)
            ->addFieldToFilter('main_table.rma_id', $id)
            ->setOrder('created_at', 'desc');

        return (bool) $collection->count();
    }

    /**
     * Current Store ID
     */
    protected $_store = null;

    /**
     * @return bool|Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->getStoreId()) {
            return Mage::app()->getDefaultStoreView();
        }
        if ($this->_store === null) {
            $this->_store = Mage::getModel('core/store')->load($this->getStoreId());
        }

        return $this->_store;
    }

    /**
     * Current Customer
     */
    protected $_customer = null;

    /**
     * @return bool|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->getCustomerId()) {
                $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            } elseif ($this->getFirstname()) {
                $this->_customer = new Varien_Object(array(
                    'firstname' => $this->getFirstname(),
                    'lastname' => $this->getLastname(),
                    'name' => $this->getFirstname().' '.$this->getLastname(),
                    'email' => $this->getEmail(),
                ));
            } else {
                $this->_customer = false;
            }
        }

        return $this->_customer;
    }

    /**
     * Current Status
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
            $this->_status->setStoreId($this->getStoreId());
        }

        return $this->_status;
    }

    /************************/

    /**
     * Connected ticket (if exists)
     */
    protected $_ticket = null;

    /**
     * @return Mirasvit_Helpdesk_Model_Ticket
     */
    public function getTicket()
    {
        if (!$this->getTicketId()) {
            return false;
        }
        if ($this->_ticket === null) {
            $this->_ticket = Mage::getModel('helpdesk/ticket')->load($this->getTicketId());
        }

        return $this->_ticket;
    }

    /**
     * Connected Credit Memo (if exists)
     */
    // @codingStandardsIgnoreStart - Not sure, perhaps, field needs to be renamed
    protected $_creditmemo_order = null;
    // @codingStandardsIgnoreEnd

    /**
     * @return bool|Mage_Core_Model_Abstract|null
     */
    public function getCreditMemo()
    {
        if (!$this->getCreditMemoId()) {
            return false;
        }

        // @codingStandardsIgnoreStart - See above
        if ($this->_creditmemo_order === null) {
            $this->_creditmemo_order = Mage::getModel('sales/order_creditmemo')->load($this->getCreditMemoId());
        }

        return $this->_creditmemo_order;
        // @codingStandardsIgnoreEnd
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Mage::getUrl('rma/rma/view', array('id' => $this->getId()));
    }

    /**
     * @return string
     */
    public function getGuestUrl()
    {
        $url = Mage::helper('rma/url')->getGuestRmaViewUrl($this);
        return $url;
    }

    /**
     * @return string
     */
    public function getGuestSuccessUrl()
    {
        if (Mage::getSingleton('rma/config')->getGeneralIsAdditionalStepAllowed()) {
            $url = Mage::getUrl('rma/rma/shipment',
                array('guest_id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        } else {
            $url = Mage::getUrl('rma/rma/success',
                array('guest_id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getGuestPrintUrl();
    }

    /**
     * @return string
     */
    public function getGuestPrintUrl()
    {
        $url = Mage::getUrl('rma/guest/print',
            array('guest_id' => $this->getGuestId(), '_store' => $this->getStoreId()));

        return $url;
    }

    /**
     * @return bool|string
     */
    public function getGuestPrintLabelUrl()
    {
        if (!$this->getReturnLabel()) {
            return false;
        }

        return Mage::getUrl('rma/guest/printlabel', array('guest_id' => $this->getGuestId(),
            '_store' => $this->getStoreId()));
    }

    /**
     * @return string
     */
    public function getBackendUrl()
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/rma_rma/edit', array('id' => $this->getId()));

        return $url;
    }

    /**
     * @return void
     */
    protected function _beforeSave()
    {
        if (
            $this->getOrigData('status_id') != $this->getStatusId()
            && in_array($this->getStatusId(), Mage::getSingleton('rma/config')->getGeneralArchivedStatusList())
        ) {
            $this->setIsArchived(true);
        }
        parent::_beforeSave();
        if (!$this->getGuestId()) {
            $this->setGuestId(md5($this->getId().Mage::helper('rma/string')->generateRandString(10)));
        }

        $config = Mage::getSingleton('rma/config');
        if (!$this->getId()) {
            $this->setIsAdminRead(true);
        }
        if (!$this->getStatusId()) {
            $this->setStatusId($config->getGeneralDefaultStatus());
        }
        if (!$this->getUserId()) {
            $this->setUserId($config->getGeneralDefaultUser());
        }
        if (!$this->getIsResolved()) {
            $status = $this->getStatus();
            if ($status->getIsRmaResolved()) {
                $this->setIsResolved(true);
            }
        }

        if ($this->getId()) {
            if (!Mage::registry('rma_created')) {
                Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_UPDATED, $this);
            }
        } else {
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_CREATED, $this);
        }
    }

    /**
     * @return bool
     */
    public function getIsShowShippingBlock()
    {
        if (!$this->getStatus()) {
            return false;
        }

        return $this->getStatus()->getIsRmaResolved();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();
        if (!$this->getIncrementId()) {
            $this->setIncrementId(Mage::helper('rma')->generateIncrementId($this));
            $this->save();
        }
    }

    /**
     * @return string
     */
    public function getShippingAddressHtml()
    {
        $items = array();

        $helper = Mage::helper('core');
        $items[] = $helper->escapeHtml($this->getFirstname().' '.$this->getLastname());
        if ($this->getEmail()) {
            $items[] = $helper->escapeHtml($this->getEmail());
        }
        if ($this->getTelephone()) {
            $items[] = $helper->escapeHtml($this->getTelephone());
        }
        if ($this->getCompany()) {
            $items[] = $helper->escapeHtml($this->getCompany());
        }
        if ($this->getStreet()) {
            $items[] = $helper->escapeHtml($this->getStreet());
        }
        if ($this->getCity()) {
            $items[] = $helper->escapeHtml($this->getCity());
        }
        if ($this->getRegion()) {
            $items[] = $helper->escapeHtml($this->getRegion());
        }
        if ($this->getPostcode()) {
            $items[] = $helper->escapeHtml($this->getPostcode());
        }
        if ($this->getCountryId()) {
            $country = Mage::getModel('directory/country')->loadByCode($this->getCountryId());
            $items[] = $country->getName();
        }

        if ($this->getOfflineAddress()) {
            $items[] = Mage::helper('rma')->convertToHtml('<br>'.$helper->escapeHtml($this->getOfflineAddress()));
        }

        return trim(implode('<br>', $items));
    }

    /**
     * @return string
     */
    public function getReturnAddress()
    {
        if ($this->getReturnAddressId()) {
            return Mage::getModel('rma/return_address')->load($this->getReturnAddressId())->getAddress();
        } else {
            return Mage::getSingleton('rma/config')->getGeneralReturnAddress($this->getStoreId());
        }
    }

    /**
     * @return string
     */
    public function getReturnAddressHtml()
    {
        return Mage::helper('rma')->convertToHtml(Mage::helper('core')->escapeHtml($this->getReturnAddress()));
    }

    /**
     * @param string                              $text
     * @param bool                                $isHtml
     * @param Mage_Customer_Model_Customer        $customer
     * @param Mage_Admin_Model_User               $user
     * @param bool                                $isNotify
     * @param bool                                $isVisible
     * @param bool|true                           $isNotifyAdmin
     * @param Mirasvit_Helpdesk_Model_Email|false $email
     *
     * @return Mirasvit_Rma_Model_Comment
     *
     * @throws Exception
     */
    public function addComment($text, $isHtml, $customer, $user, $isNotify, $isVisible, $isNotifyAdmin = true,
        $email = false
    ) {
        $comment = Mage::getModel('rma/comment')
            ->setRmaId($this->getId())
            ->setText(Mage::helper('rma/mail')->parseVariables($text, $this), $isHtml)
            ->setIsVisibleInFrontend($isVisible)
            ->setIsCustomerNotified($isNotify)
            ->save();

        if ($email) {
            $comment->setEmailId($email->getId());
            $email->setIsProcessed(true)
                  ->save();
        }
        Mage::helper('rma/attachment')->saveAttachments($comment, $email);

        if ($customer) {
            $comment->setCustomerId($customer->getId())
                    ->setCustomerName($customer->getName());
            $this->setLastReplyName($customer->getName());
            $this->setLastReplyAt(Mage::getSingleton('core/date')->gmtDate());
            $this->setIsAdminRead(false);
            if ($isNotifyAdmin) {
                Mage::helper('rma/mail')->sendNotificationAdminEmail($this, $comment);
            }
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_CUSTOMER_REPLY, $this);
        } elseif ($user) {
            $this->setLastReplyName($user->getName());
            $this->setIsAdminRead(true);

            $comment->setUserId($user->getId());
            if ($isNotify) {
                Mage::helper('rma/mail')->sendNotificationCustomerEmail($this, $comment);
            }
            //send notification about internal message
            if ($this->getUserId() != $user->getId() && !$isVisible) {
                Mage::helper('rma/mail')->sendNotificationAdminEmail($this, $comment);
            }
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_STAFF_REPLY, $this);
        }

        $comment->save();
        $this->save();

        return $comment;
    }

    /**
     * @param array $ordersId
     *
     * @return $this
     *
     * @throws Exception
     */
    public function initFromOrder($ordersId)
    {
        $this->setOrdersId($ordersId);
        //        $order = $this->getOrders()->getFirstItem();
        $collection = Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer(), false);
        $collection->addFieldToFilter('entity_id', $ordersId);

        if ($orders = Mage::app()->getRequest()->getParam('offline_orders')) {
            foreach ($orders as $data) {
                $order = Mage::helper('rma/order')->createOfflineOrder($data);
                $collection->addItem($order);
            }
        }

        if (!$collection->count()) {
            return $this;
        }

        $order = $collection->getLastItem();

        $this->setCustomerId($order->getCustomerId());
        if ($customer = $this->getCustomer()) {
            $data = $customer->getData();
            unset($data['increment_id']);
            $this->addData($data);
        } else {
            $this->setEmail($order->getCustomerEmail());
        }

        $address = $order->getShippingAddress();
        if (!$address) {
            $address = $order->getBillingAddress();
        }
        $data = $address->getData();
        if (!$address->getEmail() || trim($address->getEmail()) == '') {
            unset($data['email']);
        }
        unset($data['increment_id']);
        $this->addData($data);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * @return Mirasvit_MstCore_Model_Attachment
     */
    public function getReturnLabel()
    {
        return Mage::helper('mstcore/attachment')->getAttachment('rma_return_label', $this->getId());
    }

    /**
     * Current Backend User
     */
    protected $_user = null;

    /**
     * @return bool|Mage_Admin_Model_User|null
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
     * @return string
     */
    public function getCode()
    {
        return 'RMA-'.$this->getGuestId();
    }

    /**
     * @return Mirasvit_Rma_Model_Comment
     */
    public function getLastComment()
    {
        $collection = Mage::getModel('rma/comment')->getCollection()
            ->addFieldToFilter('rma_id', $this->getId())
            ->setOrder('comment_id', 'asc');
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        if ($this->getUser()) {
            return $this->getUser()->getName();
        } else {
            return Mage::helper('rma')->__('Unassigned');
        }
    }

    /**
     * @param string $format
     * @return string
     */
    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format).' ' .
            Mage::helper('core')->formatTime($this->getCreatedAt(), $format);
    }

    /**
     * @param string $format
     * @return string
     */
    public function getUpdatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getUpdatedAt(), $format).' ' .
            Mage::helper('core')->formatTime($this->getUpdatedAt(), $format);
    }

    /**
     * @return void
     */
    public function confirmShipping()
    {
        if ($status = Mage::helper('rma')->getStatusByCode(Mirasvit_Rma_Model_Status::PACKAGE_SENT)) {
            $this->setStatusId($status->getId());
            $this->save();
            Mage::dispatchEvent('mst_rma_changed', array('rma'=> $this));
        }
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'status_name');
    }

    /**
     * @param int $resolutionId
     * @return bool
     */
    public function getHasItemsWithResolution($resolutionId)
    {
        $items = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRmaId())
            ->addFieldToFilter('qty_requested', array('gt' => 0))
            ->addFieldToFilter('main_table.resolution_id', $resolutionId);

        return $items->count() > 0;
    }

    /**
     * @param int $reasonId
     * @return bool
     */
    public function getHasItemsWithReason($reasonId)
    {
        $items = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRmaId())
            ->addFieldToFilter('qty_requested', array('gt' => 0))
            ->addFieldToFilter('main_table.reason_id', $reasonId);

        return $items->count() > 0;
    }

    /**
     * @param int $conditionId
     * @return bool
     */
    public function getHasItemsWithCondition($conditionId)
    {
        $items = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRmaId())
            ->addFieldToFilter('qty_requested', array('gt' => 0))
            ->addFieldToFilter('main_table.condition_id', $conditionId);

        return $items->count() > 0;
    }

    /**
     * Returns block of all FedEx Labels in current RMA.
     *
     * @return array | false;
     */
    public function getFedExLabels()
    {
        $fedexLabels = array();
        $labels = Mage::getModel('rma/fedex_label')->getCollection()
            ->addFieldToFilter('rma_id', $this->getId());
        foreach ($labels as $label) {
            $trackNumber = $label->getTrackNumber();
            $fedexLabels[] = '<a target="_blank" href="'.Mage::helper('adminhtml')->getUrl('rma/guest/getFedExLabel',
                    array('label_id' => $label->getId())).'">'.
                Mage::helper('rma')->__('Download label (TRK #').
                    substr($trackNumber, 0, 3).' '.substr($trackNumber, 3, 4).' '.substr($trackNumber, 7).')</a>';
        }

        return (count($fedexLabels)) ? '<br>'.implode('<br>', $fedexLabels) : false;
    }

    /**
     * @param int $id
     * @return Mirasvit_Rma_Model_Rma
     */
    public function getRmaByGuestId($id)
    {
        return $this->getCollection()
            ->addFieldToFilter('main_table.guest_id', $id)
            ->getFirstItem();
    }
}
