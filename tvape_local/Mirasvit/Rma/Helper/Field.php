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



class Mirasvit_Rma_Helper_Field extends Mage_Core_Helper_Abstract
{
    /**
     * @param bool   $isProduct
     * @return Mirasvit_Rma_Model_Field[]|Mirasvit_Rma_Model_Resource_Field_Collection
     */
    public function getEditableCustomerCollection($isProduct = false)
    {
        return Mage::getModel('rma/field')->getCollection()
                    ->addFieldToFilter('is_active', true)
                    ->addFieldToFilter('is_product', $isProduct)
                    ->addFieldToFilter('is_editable_customer', true)
                    ->setOrder('sort_order', 'asc');
    }

    /**
     * @param string $status
     * @param bool   $isEdit
     * @param bool   $isProduct
     *
     * @return Mirasvit_Rma_Model_Field[]|Mirasvit_Rma_Model_Resource_Field_Collection
     */
    public function getVisibleCustomerCollection($status, $isEdit, $isProduct = false)
    {
        $collection = Mage::getModel('rma/field')->getCollection()
                    ->addFieldToFilter('is_active', true)
                    ->addFieldToFilter('is_product', $isProduct)
                    ->addFieldToFilter('visible_customer_status', array('like' => "%,$status,%"))
                    ->setOrder('sort_order', 'asc');
        if ($isEdit) {
            $collection->addFieldToFilter('is_editable_customer', true);
        }

        return $collection;
    }

    /**
     * @param bool   $isProduct
     * @return Mirasvit_Rma_Model_Field[]|Mirasvit_Rma_Model_Resource_Field_Collection
     */
    public function getShippingConfirmationFields($isProduct = false)
    {
        $collection = Mage::getModel('rma/field')->getCollection()
                    ->addFieldToFilter('is_active', true)
                    ->addFieldToFilter('is_product', $isProduct)
                    ->addFieldToFilter('is_show_in_confirm_shipping', true)
                    // ->addFieldToFilter('is_editable_customer', true)
                    ->setOrder('sort_order', 'asc');

        return $collection;
    }

    /**
     * @param bool   $isProduct
     * @return Mirasvit_Rma_Model_Field[]|Mirasvit_Rma_Model_Resource_Field_Collection
     */
    public function getStaffCollection($isProduct = false)
    {
        return Mage::getModel('rma/field')->getCollection()
                    ->addFieldToFilter('is_active', true)
                    ->addFieldToFilter('is_product', $isProduct)
                    ->setOrder('sort_order', 'asc');
    }

    /**
     * @param Mirasvit_Rma_Model_Field $field
     * @param bool                     $staff
     * @param bool|Varien_Object       $object
     *
     * @return array
     */
    public function getInputParams($field, $staff = true, $object = false)
    {
        $value = $object ? $object->getData($field->getCode()) : '';
        switch($field->getType()) {
            case Mirasvit_Rma_Model_Field::TYPE_CHECKBOX:
                $value = 1;
                break;
            case Mirasvit_Rma_Model_Field::TYPE_DATE:
                if ($value == '0000-00-00 00:00:00') {
                    $value = '';
                }
                break;
        }
        return array(
            'label' => Mage::helper('rma')->__($field->getName()),
            'name' => $field->getCode(),
            'required' => $staff ? $field->getIsRequiredStaff() : $field->getIsRequiredCustomer(),
            'value' => $value,
            'checked' => $object ? $object->getData($field->getCode()) : false,
            'values' => $field->getValues(true),
            'image' => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'note' => $field->getDescription(),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        );
    }

    /**
     * @param string $field
     * @param Varien_Object $object
     * @return string
     */
    public function getTemplateInputControl($field, $object)
    {
        $value = $object ? $object->getData($field->getCode()) : '';

        $fieldHtml = '<label for="'. $field->getCode() . ' "></label>';
        switch($field->getType()) {
            case Mirasvit_Rma_Model_Field::TYPE_CHECKBOX:
                $value = 1;
                break;
            case Mirasvit_Rma_Model_Field::TYPE_DATE:
                if ($value == '0000-00-00 00:00:00') {
                    $value = '';
                }
                break;
            case Mirasvit_Rma_Model_Field::TYPE_MULTILINE:
                $value = '';
                break;
            case Mirasvit_Rma_Model_Field::TYPE_SELECT:
                $value = 1;
                break;
            case Mirasvit_Rma_Model_Field::TYPE_TEXT:
                $value = '';
                break;
        }

        return $fieldHtml;
    }

    /**
     * @param Mirasvit_Rma_Model_Field $field
     * @param string $namePrefix
     * @param Varien_Object $object
     *
     * @return string
     */
    public function getInputHtml($field, $namePrefix = '', $object = null)
    {
        $params = $this->getInputParams($field, false);
        unset($params['label']);
        $className = 'Varien_Data_Form_Element_'.ucfirst(strtolower($field->getType()));
        $element = new $className($params);
        $element->setForm(new Varien_Object());
        $element->setId($field->getCode());
        $element->setName($field->getCode());
        if ($namePrefix != '') {
            $element->setName($namePrefix . '[' . $field->getCode() . ']');
        }
        $element->setNoSpan(true);
        $element->addClass($field->getType());

        // $element->setRenderer(new Mirasvit_Rma_Helper_Field_Renderer());
        // in some cases its not working without this line. but it maybe wrong for other cases.

        if ($field->getIsRequiredCustomer()) {
            $element->addClass('required-entry');
        }
        if (is_object($object)) {
            if ($object && $field->getType() != Mirasvit_Rma_Model_Field::TYPE_CHECKBOX) {
                $element->setValue($object->getData($field->getCode()));
            } else {
                $element->setChecked($object->getData($field->getCode()));
            }
        }
        //store may have wrong renderer. so we can't use ->toHtml() here;
        return $element->getDefaultHtml();
    }

    /**
     * @param array         $post
     * @param Varien_Object $object
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function processPost($post, $object)
    {
        $collection = Mage::helper('rma/field')->getEditableCustomerCollection();
        foreach ($collection as $field) {
            if (isset($post[$field->getCode()])) {
                $value = $post[$field->getCode()];
                $object->setData($field->getCode(), $value);
            }
            if ($field->getType() == 'checkbox') {
                if (!isset($post[$field->getCode()])) {
                    $object->setData($field->getCode(), 0);
                }
            } elseif ($field->getType() == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                Mage::helper('mstcore/date')->formatDateForSave($object, $field->getCode(), $format);
            }
        }
    }

    /**
     * @param Varien_Object            $object
     * @param Mirasvit_Rma_Model_Field $field
     *
     * @return bool|string
     */
    public function getValue($object, $field)
    {
        $value = $object->getData($field->getCode());
        if (!$value) {
            return false;
        }
        if ($field->getType() == 'checkbox') {
            $value = $value ? Mage::helper('rma')->__('Yes') : Mage::helper('rma')->__('No');
        } elseif ($field->getType() == 'date') {
            $value = Mage::helper('core')->formatDate($value, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        } elseif ($field->getType() == 'select') {
            $values = $field->getValues();
            $value = $values[$value];
        }

        return $value;
    }

    /**
     * @param string $code
     *
     * @return null|Mirasvit_Rma_Model_Field
     */
    public function getFieldByCode($code)
    {
        $field = Mage::getModel('rma/field')->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if ($field->getId()) {
            return $field;
        }
    }
}
