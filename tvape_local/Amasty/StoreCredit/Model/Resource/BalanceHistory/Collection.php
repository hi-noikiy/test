<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Resource_BalanceHistory_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amstcred/balanceHistory');
    }


    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinInner(array('b' => $this->getTable('amstcred/customer_balance')),
                'main_table.balance_id = b.balance_id', array('customer_id' => 'b.customer_id',
                    'website_id' => 'b.website_id',
                    'base_currency_code' => 'b.base_currency_code'));
        return $this;
    }


    public function addWebsiteFilter($websiteIds)
    {
        $this->getSelect()->where('b.website_id IN (?)', $websiteIds);
        return $this;
    }


    public function addCustomerData()
    {
        $customerEntity = Mage::getResourceSingleton('customer/customer');
        $attrFirstname = $customerEntity->getAttribute('firstname');
        $attrFirstnameId = (int)$attrFirstname->getAttributeId();
        $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();

        $attrLastname = $customerEntity->getAttribute('lastname');
        $attrLastnameId = (int)$attrLastname->getAttributeId();
        $attrLastnameTableName = $attrLastname->getBackend()->getTable();

        $attrEmail = $customerEntity->getAttribute('email');
        $attrEmailTableName = $attrEmail->getBackend()->getTable();

        $adapter = $this->getSelect()->getAdapter();
        $customerName = $adapter->getConcatSql(array('cust_fname.value', 'cust_lname.value'), ' ');
        $this->getSelect()
            ->joinInner(
                array('cust_email' => $attrEmailTableName),
                'cust_email.entity_id = b.customer_id',
                array('customer_email' => 'cust_email.email')
            )
            ->joinInner(
                array('cust_fname' => $attrFirstnameTableName),
                implode(' AND ', array(
                    'cust_fname.entity_id = b.customer_id',
                    $adapter->quoteInto('cust_fname.attribute_id = ?', (int)$attrFirstnameId),
                )),
                array('firstname' => 'cust_fname.value')
            )
            ->joinInner(
                array('cust_lname' => $attrLastnameTableName),
                implode(' AND ', array(
                    'cust_lname.entity_id = b.customer_id',
                    $adapter->quoteInto('cust_lname.attribute_id = ?', (int)$attrLastnameId)
                )),
                array(
                    'lastname' => 'cust_lname.value',
                    'customer_name' => $customerName
                )
            );

        $this->_joinedFields['customer_name'] = $customerName;
        $this->_map['fields']['customer_name'] = $customerName;
        $this->_joinedFields['email'] = 'cust_email.email';

        return $this;
    }
}
