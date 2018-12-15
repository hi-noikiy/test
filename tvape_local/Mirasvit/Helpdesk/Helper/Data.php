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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_jstranslation = false;

    /**
     * Traverses two-dimensional array $haystack, and if element (which is also array) has $key, tests, whether it equals to $needle.
     *
     * @param string $needle
     * @param array  $haystack
     * @param string $key
     *
     * @return bool
     */
    public function checkValueByKey($needle, $haystack, $key)
    {
        foreach ($haystack as $element) {
            if (isset($element[$key]) && $element[$key] == $needle) {
                return true;
            }
        }

        return false;
    }

    public function toAdminUserOptionArray($emptyOption = false)
    {
        $arr = Mage::getModel('admin/user')->getCollection()->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[] = array('value' => $value['user_id'], 'label' => $value['firstname'].' '.$value['lastname']);
        }
        if ($emptyOption) {
            array_unshift($result, array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --')));
        }

        return $result;
    }

    public function getAdminUserOptionArray($emptyOption = false)
    {
        $users = Mage::getModel('admin/user')->getCollection();
        $users->getSelect()
                ->join(array('department' => Mage::getSingleton('core/resource')->getTableName('helpdesk/department_user')), 'department.du_user_id = main_table.user_id', array('helpdesk_userid' => 'department.department_user_id'))
                ->group('main_table.user_id');
        $arr = $users->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[$value['user_id']] = $value['firstname'].' '.$value['lastname'];
        }
        if ($emptyOption) {
            $result[0] = Mage::helper('helpdesk')->__('-- Please Select --');
        }

        return $result;
    }

    // public function getCoreStoreOptionArray() {
    //     $arr = Mage::getModel('core/store')->getCollection()->toArray();
    //     foreach ($arr['items'] as $value) {
    //         $result[$value['store_id']] = $value['name'];
    //     }
    //     return $result;
    // }

    public function toAdminRoleOptionArray($emptyOption = false)
    {
        $arr = Mage::getModel('admin/role')->getCollection()
                    ->addFieldToFilter('role_type', 'G')
                    ->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[] = array('value' => $value['role_id'], 'label' => $value['role_name']);
        }
        if ($emptyOption === true) {
            $emptyOption = '-- Please Select --';
        }
        if ($emptyOption) {
            array_unshift($result, array('value' => 0, 'label' => Mage::helper('helpdesk')->__($emptyOption)));
        }

        return $result;
    }

    public function getAdminRoleOptionArray($emptyOption = false)
    {
        $arr = Mage::getModel('admin/role')->getCollection()
                    ->addFieldToFilter('role_type', 'G')
                    ->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[$value['role_id']] = $value['role_name'];
        }
        if ($emptyOption === true) {
            $emptyOption = '-- Please Select --';
        }
        if ($emptyOption) {
            $result[0] = Mage::helper('helpdesk')->__($emptyOption);
        }

        return $result;
    }

    /************************/

    public function getCoreStoreOptionArray($emptyOption = false)
    {
        $result = array();
        $arr = Mage::getModel('core/store')->getCollection()->toArray();
        foreach ($arr['items'] as $value) {
            $result[$value['store_id']] = $value['name'];
        }
        if ($emptyOption) {
            $result[0] = Mage::helper('helpdesk')->__('-- Please Select --');
        }

        return $result;
    }

    public function getAdminOwnerOptionArray($emptyOption = false, $storeId = false)
    {
        $result = array();
        if ($emptyOption) {
            $result['0_0'] = Mage::helper('helpdesk')->__('-- Please Select --');
        }
        $collection = Mage::getModel('helpdesk/department')->getCollection()
                        ->addFieldToFilter('is_active', true)
                        ->setOrder('sort_order', 'asc');
        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }
        foreach ($collection as $department) {
            $result[$department->getId().'_0'] = $department->getName();
            foreach ($department->getUsers() as $user) {
                $result[$department->getId().'_'.$user->getId()] = '- '.$user->getFirstname().' '.$user->getLastname();
            }
        }

        return $result;
    }

    public function getCustomerArray($q = false, $customerId = false, $addressId = false)
    {
        $firstnameId = Mage::getModel('eav/entity_attribute')->loadByCode(1, 'firstname')->getId();
        $lastnameId = Mage::getModel('eav/entity_attribute')->loadByCode(1, 'lastname')->getId();

        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->getSelect()->limit(20);

        if ($q) {
            $resource = Mage::getSingleton('core/resource');
            $collection->getSelect()
                ->joinLeft(
                    array('varchar1' => $resource->getTableName('customer/entity').'_varchar'),
                    'e.entity_id = varchar1.entity_id and varchar1.attribute_id = '.$firstnameId,
                    array('firstname' => 'varchar1.value')
                )
                ->joinLeft(
                    array('varchar2' => $resource->getTableName('customer/entity').'_varchar'),
                    'e.entity_id = varchar2.entity_id and varchar2.attribute_id = '.$lastnameId,
                    array('lastname' => 'varchar2.value')
                )->joinLeft(
                    array('orders' => $resource->getTableName('sales/order')),
                    'e.entity_id = orders.customer_id',
                    array('order' => 'orders.increment_id')
                )->group('e.entity_id');
            $search = Mage::getModel('helpdesk/search');
            $search->setSearchableCollection($collection);
            $search->setSearchableAttributes(array(
                'e.entity_id' => 0,
                'e.email' => 0,
                'firstname' => 0,
                'lastname' => 0,
                'order' => 0,
            ));
            $search->setPrimaryKey('entity_id');
            $search->joinMatched($q, $collection, 'e.entity_id');
        }

        if ($customerId !== false) {
            $collection->addFieldToFilter('entity_id', $customerId);
        }

        $result = array();
        foreach ($collection as $customer) {
            $result[] = array(
                'id' => $customer->getId(),
                'name' => $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')',
                'email' => $customer->getEmail(),
            );
        }

        if (Mage::getVersion() <= '1.4.1.1') {
            //unregistered search
            $collection = Mage::getModel('sales/quote_address')->getCollection();
            $collection
                ->getSelect()
                ->group('email')
                ->limit(20);
            if ($q) {
                $search = Mage::getModel('helpdesk/search');
                $search->setSearchableCollection($collection);
                $search->setSearchableAttributes(array(
                    'email' => 0,
                    'firstname' => 0,
                    'lastname' => 0,
                ));
                $search->setPrimaryKey('address_id');
                $search->joinMatched($q, $collection, 'address_id');
            }
            if ($addressId !== false) {
                $collection->addFieldToFilter('address_id', $addressId);
            }
            foreach ($collection as $address) {
                if (!$this->checkValueByKey($address->getEmail(), $result, 'email')) {
                    // Fix to have proper order id extraction
                    $orderId = Mage::getModel('sales/order')->loadByAttribute('quote_id', $address->getQuoteId())->getId();
                    $result[] = array(
                        'id' => 'address_'.$address->getId(),
                        'order_id' => $orderId,
                        'name' => $address->getFirstname().' '.$address->getLastname().' ('.$address->getEmail().') [unregistered]',
                        'email' => $address->getEmail(),
                    );
                }
            }
            // print_r($result);die;
        } else {
            //unregstered search
            $collection = Mage::getModel('sales/order_address')->getCollection();
            $collection
                ->getSelect()
                ->group('email')
                ->limit(20);
            if ($q) {
                $search = Mage::getModel('helpdesk/search');
                $search->setSearchableCollection($collection);
                $search->setSearchableAttributes(array(
                    'email' => 0,
                    'firstname' => 0,
                    'lastname' => 0,
                ));
                $search->setPrimaryKey('entity_id');
                $search->joinMatched($q, $collection, 'main_table.entity_id');
            }
            if ($addressId !== false) {
                $collection->addFieldToFilter('main_table.entity_id', $addressId);
            }

            foreach ($collection as $address) {
                if (!$address->getEmail()) {
                    continue;
                }
                if (!$this->checkValueByKey($address->getEmail(), $result, 'email')) {
                    $orderId = Mage::getModel('sales/order')->loadByAttribute('quote_id', $address->getQuoteId())->getId();
                    $result[] = array(
                        // Fix to have proper order id extraction
                        'id' => 'address_'.$address->getId(),
                        'order_id' => $orderId,
                        'name' => $address->getFirstname().' '.$address->getLastname().' ('.$address->getEmail().') [unregistered]',
                        'email' => $address->getEmail(),
                    );
                }
            }
        }

        return $result;
    }

    public function getOrderArray($customerEmail, $customerId = false)
    {
        $orders = array();
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addAttributeToSelect('*')
            ->setOrder('created_at', 'desc')
            ;
        if ($customerId) {
            $collection->addFieldToFilter(
                array('customer_email', 'customer_id'), array($customerEmail, $customerId)
            );
        } else {
            $collection->addFieldToFilter('customer_email', $customerEmail);
        }
        /** @var Mage_Sales_Model_Order $order */
        foreach ($collection as $order) {
            $orders[] = array(
                'id' => $order->getId(),
                'name' => $this->getOrderLabel($order),
            );
        }

        return $orders;
    }

    public function findCustomer($q)
    {
        $customers = $this->getCustomerArray($q);
        foreach ($customers as $key => $customer) {
            $customerId = false;
            if (isset($customer['id'])) {
                $customerId = (int) $customer['id'];
            }
            $orders = $this->getOrderArray($customer['email'], $customerId);
            array_unshift($orders, array('id' => 0, 'name' => $this->__('Unassigned')));
            $customers[$key]['orders'] = $orders;
        }

        return $customers;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     *
     * @throws Mage_Core_Exception
     */
    public function saveAttachments($message)
    {
        if (!isset($_FILES['attachment']['name'])) {
            return;
        }
        $maxSize = (int) ($this->file_upload_max_size() / 1000000);
        $i = 0;
        foreach ($_FILES['attachment']['name'] as $name) {
            // echo $name;
            if ($name == '') {
                continue;
            }

            if ($_FILES['attachment']['tmp_name'][$i] == '') {
                Mage::throwException("Can't upload file $name . Max allowed upload size is ".$maxSize.' MB.');
            }
            //@tofix - need to check for max upload size and alert error
            $body = file_get_contents(addslashes($_FILES['attachment']['tmp_name'][$i]));
            $size = $_FILES['attachment']['size'][$i];

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowedFileExtensions = $this->getConfig()->getGeneralFileAllowedExtensions();
            if (count($allowedFileExtensions) && !in_array($ext, $allowedFileExtensions)) {
                continue;
            }
            $sizeLimit = $this->getAllowedSize();
            if ($sizeLimit && $size > $sizeLimit) {
                continue;
            }

            //create and save attachment
            Mage::getModel('helpdesk/attachment')
                ->setName($name)
                ->setType(strtoupper($_FILES['attachment']['type'][$i]))
                ->setSize($size)
                ->setMessageId($message->getId())
                ->setBody($body)
                ->save();
            ++$i;
        }
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }
    /**
     * @return int
     */
    public function getAllowedSize()
    {
        return $this->getConfig()->getGeneralFileSizeLimit()  * 1024 * 1024;
    }

    /**
     * get max upload size in bytes.
     *
     * @return float
     */
    public function file_upload_max_size()
    {
        static $max_size = -1;
        if ($max_size < 0) {
            $max_size = $this->parse_size(ini_get('post_max_size'));
            $upload_max = $this->parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }

        return $max_size;
    }

    public function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * @param Mage_Sales_Model_Order|int $order
     * @param bool|string                $url
     *
     * @return string
     */
    public function getOrderLabel($order, $url = false)
    {
        if (!is_object($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }
        $res = "#{$order->getRealOrderId()}";
        if ($url) {
            $res = "<a href='{$url}' target='_blank'>$res</a>";
        }
        $res .= Mage::helper('helpdesk')->__(' at %s (%s) - %s',
            Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium'),
            strip_tags($order->formatPrice($order->getGrandTotal())),
            Mage::helper('helpdesk')->__(ucwords($order->getStatus()))
        );

        return $res;
    }

    public function getHistoryHtml($ticket)
    {
        /** @var Mirasvit_Helpdesk_Block_Email_History $block */
        $block = Mage::app()->getLayout()->createBlock('helpdesk/email_history');
        $block->setTemplate('mst_helpdesk/email/history.phtml');
        $block->setTicket($ticket);

        return $block->toHtml();
    }

    public function getCssFile()
    {
        if (file_exists(Mage::getBaseDir('skin').'/frontend/base/default/css/mirasvit/helpdesk/custom.css')) {
            return 'css/mirasvit/helpdesk/custom.css';
        }
        if (Mage::getVersion() >= '1.9.0.0') {
            return 'css/mirasvit/helpdesk/rwd.css';
        }

        return 'css/mirasvit/helpdesk/fixed.css';
    }

    /**
     * @return bool|Mirasvit_Helpdesk_Model_User
     */
    public function getHelpdeskUser($user)
    {
        if (!$user->getId()) {
            return false;
        }
        $userId = $user->getId();
        try {
            $resource = Mage::getSingleton('core/resource');
            $query = 'SELECT COUNT(*) FROM '.$resource->getTableName('helpdesk/user').' WHERE user_id='.$userId;
            if ($resource->getConnection('core_read')->fetchOne($query) == 0) {
                $query = 'INSERT INTO '.$resource->getTableName('helpdesk/user').' (user_id) VALUES ('.$userId.')';
                $resource->getConnection('core_write')->query($query);
            }
        } catch (Exception $e) { //it's possible that migrations were not run at this point. Table has not been created yet.
            Mage::logException($e);

            return false;
        }
        $helpdeskUser = Mage::getModel('helpdesk/user')->load($userId);
        $helpdeskUser->setId($userId);

        return $helpdeskUser;
    }
}
