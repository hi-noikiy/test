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
 * @method Mirasvit_Rma_Model_Resource_Field_Collection|Mirasvit_Rma_Model_Field[] getCollection()
 * @method Mirasvit_Rma_Model_Field load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Field setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Field setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Field getResource()
 * @method string getCode();
 * @method $this setCode(string $param);
 * @method string getType();
 * @method $this setType(string $param);
 * @method bool getIsRequiredStaff();
 * @method $this setIsRequiredStaff(bool $param);
 * @method bool getIsRequiredCustomer();
 * @method $this setIsRequiredCustomer(bool $param);
 */
class Mirasvit_Rma_Model_Field extends Mage_Core_Model_Abstract
{
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_DATE = 'date';
    const TYPE_MULTILINE = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_TEXT = 'text';

    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/field');
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
    public function getName()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'name');
    }

    /**
     * @param string $value
     * @return Mirasvit_Rma_Model_Field
     */
    public function setName($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'name', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'description');
    }

    /**
     * @param string $value
     * @return Mirasvit_Rma_Model_Field
     */
    public function setDescription($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'description', $value);

        return $this;
    }

    // public function getValues()
    // {
    //     return Mage::helper('rma/storeview')->getStoreViewValue($this, 'values');
    // }

    /**
     * @param string $value
     * @return Mirasvit_Rma_Model_Field
     */
    public function setValues($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'values', $value);

        return $this;
    }

    /**
     * @param array $data
     * @return Varien_Object
     */
    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        if (isset($data['description']) && strpos($data['description'], 'a:') !== 0) {
            $this->setDescription($data['description']);
            unset($data['description']);
        }

        if (isset($data['values']) && strpos($data['values'], 'a:') !== 0) {
            $this->setValues($data['values']);
            unset($data['values']);
        }

        return parent::addData($data);
    }
    /************************/

    /**
     * @param bool $emptyOption
     * @return array|null
     */
    public function getValues($emptyOption = false)
    {
        $values = Mage::helper('rma/storeview')->getStoreViewValue($this, 'values');
        $arr = explode("\n", $values);
        $values = array();
        foreach ($arr as $value) {
            $value = explode('|', $value);
            if (count($value) >= 2) {
                $values[trim($value[0])] = trim($value[1]);
            }
        }
        if ($emptyOption) {
            $res = array();
            $res[] = array('value' => '', 'label' => Mage::helper('rma')->__('-- Please Select --'));
            foreach ($values as $index => $value) {
                $res[] = array(
                   'value' => $index,
                   'label' => $value,
                );
            }
            $values = $res;
        }

        return $values;
    }

    /**
     * @return bool
     */
    public function getVisibleCustomerStatus()
    {
        if (is_string($this->getData('visible_customer_status'))) {
            $this->setData('visible_customer_status', explode(',', $this->getData('visible_customer_status')));
        }

        return $this->getData('visible_customer_status');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setIsNew(true);
        }

        return parent::_beforeSave();
    }

    /**
     * @return void
     */
    protected function createRealField()
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $fieldType = 'TEXT';
        if ($this->getType() == 'date') {
            $fieldType = 'TIMESTAMP';
        }

        if ($this->getIsProduct()) {
            $tableName = $resource->getTableName('rma/item');
            $query = "ALTER TABLE `{$tableName}` ADD `{$this->getCode()}` " . $fieldType . ';';
            $tableName = $resource->getTableName('rma/offline_item');
            $query .= "ALTER TABLE `{$tableName}` ADD `{$this->getCode()}` " . $fieldType . ';';
        } else {
            $tableName = $resource->getTableName('rma/rma');
            $query = "ALTER TABLE `{$tableName}` ADD `{$this->getCode()}` " . $fieldType;
        }

        $writeConnection->query($query);
        $writeConnection->resetDdlCache();
    }

    /**
     * @param bool $useOrigData
     * @return void
     */
    protected function removeRealField($useOrigData = false)
    {
        $fieldMark = ($useOrigData) ? $this->getOrigData('is_product') : $this->getIsProduct();
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        if ($fieldMark) {
            $tableName = $resource->getTableName('rma/item');
            $query = "ALTER TABLE `{$tableName}` DROP `{$this->getCode()}`;";
            $tableName = $resource->getTableName('rma/offline_item');
            $query .= "ALTER TABLE `{$tableName}` DROP `{$this->getCode()}`;";
        } else {
            $tableName = $resource->getTableName('rma/rma');
            $query = "ALTER TABLE `{$tableName}` DROP `{$this->getCode()}`";
        }
        $writeConnection->query($query);
        $writeConnection->resetDdlCache();

    }

    /**
     * @return void
     */
    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();

        if ($this->getIsNew()) {
            $this->createRealField();
        } else {
            if ($this->getOrigData('is_product') != $this->getIsProduct()) {
                $this->removeRealField(true);
                $this->createRealField();
            }
        }
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeDelete()
    {
        $field = Mage::getModel('rma/field')->load($this->getId());
        $this->setDbCode($field->getCode());

        return parent::_beforeDelete();
    }

    /**
     * @return void
     */
    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();
        $this->removeRealField();
    }
}
