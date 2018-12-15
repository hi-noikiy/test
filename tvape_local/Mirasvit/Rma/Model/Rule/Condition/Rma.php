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



class Mirasvit_Rma_Model_Rule_Condition_Rma extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * @return Mirasvit_Rma_Model_Rule_Condition_Rma
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'last_message' => Mage::helper('rma')->__('Last message body'),
            'created_at' => Mage::helper('rma')->__('Created At'),
            'updated_at' => Mage::helper('rma')->__('Updated At'),
            'store_id' => Mage::helper('rma')->__('Store'),
            'old_status_id' => Mage::helper('rma')->__('Status (before change)'),
            'status_id' => Mage::helper('rma')->__('Status'),
            'old_user_id' => Mage::helper('rma')->__('Owner (before change)'),
            'user_id' => Mage::helper('rma')->__('Owner'),
            'last_reply_by' => Mage::helper('rma')->__('Last Reply By'),
            'old_is_archived' => Mage::helper('rma')->__('Is Archived (before change)'),
            'is_archived' => Mage::helper('rma')->__('Is Archived'),
            'hours_since_created_at' => Mage::helper('rma')->__('Hours since Created'),
            'hours_since_updated_at' => Mage::helper('rma')->__('Hours since Updated'),
            'hours_since_last_reply_at' => Mage::helper('rma')->__('Hours since Last reply'),
            'items_have_reason' => Mage::helper('rma')->__('Items have reason'),
            'items_have_condition' => Mage::helper('rma')->__('Items have condition'),
            'items_have_resolution' => Mage::helper('rma')->__('Items have resolution'),
        );

        $fields = Mage::getModel('rma/field')->getCollection()
            ->setOrder('sort_order');

        foreach ($fields as $field) {
            $attributes['old_'.$field->getCode()] = Mage::helper('rma')->__('%s (before change)', $field->getName());
            $attributes[$field->getCode()] = $field->getName();
        }

        // asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        $attrCode = $this->getAttribute();
        if (strpos($attrCode, '_id') || $attrCode == 'last_reply_by' || strpos($attrCode, '_archived')
            || strpos($attrCode, 'items_have_') === 0) {
            return 'select';
        }

        if ($field = $this->getCustomFieldByAttributeCode($attrCode)) {
            if ($field->getType() == 'select') {
                return 'select';
            }
        }

        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getInputType()) {
            case 'string':
                return 'text';
        }

        return $this->getInputType();
    }

    /**
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        /* @var Mirasvit_Rma_Model_Rma $object */
        $attrCode = $this->getAttribute();
        if (strpos($attrCode, 'old_') === 0) {
            $attrCode = str_replace('old_', '', $attrCode);
            $value = $object->getOrigData($attrCode);
        } elseif ($attrCode == 'last_message') {
            $value = $object->getLastComment()->getTextHtml();
        } elseif ($attrCode == 'last_reply_by') {
            if ($lastMessage = $object->getLastComment()) {
                $value = $lastMessage->getTriggeredBy();
            } else {
                $value = false;
            }
        } elseif (strpos($attrCode, 'items_have_') === 0) {
            $value = $this->getValueParsed();
            if (strpos($attrCode, 'reason')) {
                $validatedValue = $object->getHasItemsWithReason($value);
            } elseif (strpos($attrCode, 'condition')) {
                $validatedValue = $object->getHasItemsWithCondition($value);
            } elseif (strpos($attrCode, 'resolution')) {
                $validatedValue = $object->getHasItemsWithResolution($value);
            } else {
                return false;
            }
            if ($validatedValue && $this->getOperatorForValidate() == '==') {
                return $validatedValue;
            } else {
                return false;
            }
        } elseif (strpos($attrCode, 'hours_since_') === 0) {
            $attrCode = str_replace('hours_since_', '', $attrCode);
            if ($timestamp = $object->getData($attrCode)) {
                $diff = abs(strtotime(Mage::getModel('core/date')->gmtDate()) - strtotime($timestamp));
            } else {
                $diff = abs(strtotime(Mage::getModel('core/date')->gmtDate()) - strtotime($object->getUpdatedAt()));
            }
            $value = round($diff / 60 / 60);
        } else {
            $value = $object->getData($attrCode);
        }
        if (strpos($attrCode, '_id')) {
            $value = (int) $value; //нам это нужно чтоб приводить пустое значение к нулю и далее сравнивать
        }

        return $this->validateAttribute($value);
    }

    /**
     * @return Mirasvit_Rma_Model_Rule_Condition_Rma
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }
        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        $addNotEmpty = true;
        $field = $this->getCustomFieldByAttributeCode($this->getAttribute());

        if ($field && $field->getType() == 'select') {
            $selectOptions = $field->getValues();
        } else {
            switch ($this->getAttribute()) {
                case 'status_id':
                case 'old_status_id':
                    $selectOptions = Mage::getModel('rma/status')->getCollection()->getOptionArray();
                    break;
                case 'user_id':
                case 'old_user_id':
                    $selectOptions = Mage::helper('rma')->getAdminUserOptionArray();
                    break;
                case 'store_id':
                    $selectOptions = Mage::helper('rma')->getCoreStoreOptionArray();
                    break;
                case 'is_archived':
                    $selectOptions = Mage::helper('rma')->getBooleanOptionArray();
                    $addNotEmpty = false;
                    break;
                case 'old_is_archived':
                    $selectOptions = Mage::helper('rma')->getBooleanOptionArray();
                    $addNotEmpty = false;
                    break;
                case 'last_reply_by':
                    $selectOptions = array(
                        Mirasvit_Rma_Model_Config::CUSTOMER => Mage::helper('rma')->__('Customer'),
                        Mirasvit_Rma_Model_Config::USER => Mage::helper('rma')->__('Staff'),
                    );
                    $addNotEmpty = false;
                    break;
                case 'items_have_reason':
                    $selectOptions = Mage::helper('rma')->getReasonOptionArray();
                    $addNotEmpty = false;
                    break;
                case 'items_have_resolution':
                    $selectOptions = Mage::helper('rma')->getResolutionOptionArray();
                    $addNotEmpty = false;
                    break;
                case 'items_have_condition':
                    $selectOptions = Mage::helper('rma')->getConditionOptionArray();
                    $addNotEmpty = false;
                    break;
                default:
                    return $this;
            }
        }
        if ($addNotEmpty) {
            $selectOptions = array(0 => '(not set)') + $selectOptions;
            // array_unshift($selectOptions, '(not set)');
        }

        $nextOptions = array();
        foreach ($selectOptions as $key => $value) {
            $nextOptions[] = array('value' => $key, 'label' => $value);
        }
        $selectOptions = $nextOptions;

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = array();
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve value by option.
     *
     * @param string $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option'.($option !== null ? '/'.$option : ''));
    }

    /**
     * Retrieve select option values.
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        return $this->getData('value_select_options');
    }

    /**
     * @return string
     */
    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }

    /**
     * @param string $attrCode
     * @return Mirasvit_Rma_Model_Field
     */
    protected function getCustomFieldByAttributeCode($attrCode)
    {
        if (strpos($attrCode, 'f_') === 0 || strpos($attrCode, 'old_f_') === 0) {
            $attrCode = str_replace('old_f_', 'f_', $attrCode);

            if ($field = Mage::helper('rma/field')->getFieldByCode($attrCode)) {
                return $field;
            }
        }
    }
}
