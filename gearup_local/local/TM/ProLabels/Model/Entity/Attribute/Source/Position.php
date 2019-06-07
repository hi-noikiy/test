<?php
class TM_ProLabels_Model_Entity_Attribute_Source_Position extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    protected $_optionArray;

    public function getAllOptions()
    {
        if (!$this->_options)
        {
            $this->_options = array(
                array(
                    'value' => 'top-left',
                    'label' => Mage::helper('adminhtml')->__('top-left')
                ),
                array(
                    'value' => 'top-center',
                    'label' => Mage::helper('adminhtml')->__('top-center')
                ),
                array(
                    'value' => 'top-right',
                    'label' => Mage::helper('adminhtml')->__('top-right')
                ),
                array(
                    'value' => 'middle-left',
                    'label' => Mage::helper('adminhtml')->__('middle-left')
                ),
                array(
                    'value' => 'middle-center',
                    'label' => Mage::helper('adminhtml')->__('middle-center')
                ),
                array(
                    'value' => 'middle-right',
                    'label' => Mage::helper('adminhtml')->__('middle-right')
                ),
                array(
                    'value' => 'bottom-left',
                    'label' => Mage::helper('adminhtml')->__('bottom-left')
                ),
                array(
                    'value' => 'bottom-center',
                    'label' => Mage::helper('adminhtml')->__('bottom-center')
                ),
                array(
                    'value' => 'bottom-right',
                    'label' => Mage::helper('adminhtml')->__('bottom-right')
                ),
                array(
                    'value' => 'content',
                    'label' => Mage::helper('prolabels')->__('content')
                ),
                array(
                    'value' => 'hidden',
                    'label' => Mage::helper('prolabels')->__('hidden')
                )
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        if (!isset($this->_optionArray)) {
            $this->_optionArray = array();
            foreach ($this->getAllOptions() as $value) {
                $this->_optionArray[$value['value']] = $value['label'];
            }
        }

        return $this->_optionArray;
    }
}
