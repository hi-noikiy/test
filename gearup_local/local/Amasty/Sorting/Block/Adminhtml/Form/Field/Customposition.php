<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


use Amasty_Sorting_Model_System_Config_Backend_Customposition as PositionModel;

class Amasty_Sorting_Block_Adminhtml_Form_Field_Customposition
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /** @var  Amasty_Sorting_Block_Adminhtml_Form_Field_Method*/
    protected $methodRenderer;

    /**
     * @return Amasty_Sorting_Block_Adminhtml_Form_Field_Method
     */
    protected function getMethodRenderer()
    {
        if (!$this->methodRenderer) {
            $this->methodRenderer = $this->getLayout()
                ->createBlock(
                    'amsorting/adminhtml_form_field_method',
                    '',
                    array('is_render_to_js_template' => true)
                );
        }

        return $this->methodRenderer;
    }

    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            PositionModel::METHOD, array(
                'label' => Mage::helper('amsorting')->__('Sorting Option'),
                'renderer' => $this->getMethodRenderer()
            )
        );
        $this->addColumn(
            PositionModel::CUSTOM_POSITION, array(
                'label' => Mage::helper('amsorting')->__('Custom Option Position'),
                'style' => 'width:100px',
            )
        );
        $this->_addAfter = false;
    }

    /**
     * @param Varien_Object $row
     * @return void
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'
            . $this->getMethodRenderer()->calcOptionHash($row->getData(PositionModel::METHOD)),
            'selected="selected"'
        );
    }
}
