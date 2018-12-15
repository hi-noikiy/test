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



class Mirasvit_Helpdesk_Helper_Field extends Mage_Core_Helper_Abstract
{
    /**
     * @return Mirasvit_Helpdesk_Model_Field[]|Mirasvit_Helpdesk_Model_Resource_Field_Collection
     */
    public function getEditableCustomerCollection()
    {
        return Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->addFieldToFilter('is_editable_customer', true)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('sort_order');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Field[]|Mirasvit_Helpdesk_Model_Resource_Field_Collection
     */
    public function getVisibleCustomerCollection()
    {
        return Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->addFieldToFilter('is_visible_customer', true)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('sort_order');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Field[]|Mirasvit_Helpdesk_Model_Resource_Field_Collection
     */
    public function getContactFormCollection()
    {
        return Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->addFieldToFilter('is_visible_contact_form', true)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('sort_order');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Field[]|Mirasvit_Helpdesk_Model_Resource_Field_Collection
     */
    public function getStaffCollection()
    {
        return Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Field[]|Mirasvit_Helpdesk_Model_Resource_Field_Collection
     */
    public function getActiveCollection()
    {
        $activeFields = Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order');

        if(Mage::app()->getStore()->getId() > 0) {
            $activeFields->addStoreFilter(Mage::app()->getStore()->getId());
        }

        return $activeFields;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Field       $field
     * @param bool                                $staff
     * @param bool|Mirasvit_Helpdesk_Model_Ticket $ticket
     *
     * @return array
     */
    public function getInputParams($field, $staff = true, $ticket = false)
    {
        if ($ticket) {
            $field->setStoreId($ticket->getStore()->getId());
        } else {
            $field->setStoreId(Mage::app()->getStore()->getId());
        }

        return array(
            'label' => Mage::helper('helpdesk')->__($field->getName()),
            'name' => $field->getCode(),
            'required' => $staff ? $field->getIsRequiredStaff() : $field->getIsRequiredCustomer(),
            'value' => $field->getType() == 'checkbox' ? 1 : ($ticket ? $ticket->getData($field->getCode()) : ''),
            'checked' => $ticket ? $ticket->getData($field->getCode()) : false,
            'values' => $field->getValues(true),
            'image' => (Mage::app()->getStore()->isAdmin()) ? Mage::getDesign()->getSkinUrl('images/grid-cal.gif') : Mage::getDesign()->getSkinUrl('images/calendar.gif'),
            'note' => $field->getDescription(),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        );
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Field $field
     *
     * @return string
     */
    public function getInputHtml($field)
    {
        $params = $this->getInputParams($field, false);
        unset($params['label']);
        $className = 'Varien_Data_Form_Element_'.ucfirst(strtolower($field->getType()));
        /** @var Varien_Data_Form_Element_Abstract $element */
        $element = new $className($params);
        $element->setForm(new Varien_Object());
        $element->setId($field->getCode());
        $element->setNoSpan(true);
        if ($field->getIsRequiredCustomer()) {
            $element->addClass('required-entry');
        }

        return $element->toHtml();
    }

    /**
     * @param array                          $post
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     *
     * @throws Mage_Core_Exception
     */
    public function processPost($post, $ticket)
    {
        $collection = Mage::helper('helpdesk/field')->getActiveCollection();
        foreach ($collection as $field) {
            if (isset($post[$field->getCode()])) {
                $value = $post[$field->getCode()];
                $ticket->setData($field->getCode(), $value);
            }
            if ($field->getType() == 'checkbox') {
                if (!isset($post[$field->getCode()])) {
                    $ticket->setData($field->getCode(), 0);
                }
            } elseif ($field->getType() == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                Mage::helper('mstcore/date')->formatDateForSave($ticket, $field->getCode(), $format);
            }
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mirasvit_Helpdesk_Model_Field  $field
     *
     * @return bool|string
     */
    public function getValue($ticket, $field)
    {
        $value = $ticket->getData($field->getCode());
        if (!$value) {
            return false;
        }
        if ($field->getType() == 'checkbox') {
            $value = $value ? Mage::helper('helpdesk')->__('Yes') : Mage::helper('helpdesk')->__('No');
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
     * @return Mirasvit_Helpdesk_Model_Field
     */
    public function getFieldByCode($code)
    {
        $field = Mage::getModel('helpdesk/field')->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if ($field->getId()) {
            return $field;
        }
    }
}
