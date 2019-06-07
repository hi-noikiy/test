<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Content
    extends TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Abstract
{
    /**
     * Class constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setFieldPrefix('product_');
    }

    protected function _prepareForm()
    {
        $model = $this->_getLabelModel();
        $form = $this->getForm();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array(
                'legend' => Mage::helper('prolabels')->__('Content (Product Page)'),
                'class' => 'fieldset-wide'
            )
        );
        $this->_addElementTypes($fieldset); //register own image element
        $prefix = $this->getFieldPrefix();
        $fieldset->addField($prefix.'position', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Position'),
            'title'     => Mage::helper('prolabels')->__('Position'),
            'name'      => $prefix.'position',
            'options'   => Mage::getSingleton('prolabels/entity_attribute_source_position')->toOptionArray()
        ));

        $fieldset->addField($prefix.'image', 'image', array(
            'name'      => $prefix.'image',
            'label'     => Mage::helper('prolabels')->__('Image'),
            'title'     => Mage::helper('prolabels')->__('Image')
        ));

        $fieldset->addField($prefix.'image_text', 'text', array(
            'name'      => $prefix.'image_text',
            'label'     => Mage::helper('prolabels')->__('Image Text'),
            'title'     => Mage::helper('prolabels')->__('Image Text'),
            'after_element_html' => '<small>#attr:attribute_code# or #discount_percent# or #discount_amount# or #special_price# or #special_date# or #final_price# or #price# or #product_name# or #product_sku# or #stock_item#</small>',
        ));

        $fieldset->addField($prefix.'custom_url', 'text', array(
            'name'      => $prefix.'custom_url',
            'label'     => Mage::helper('prolabels')->__('Label Custom Url'),
            'title'     => Mage::helper('prolabels')->__('Label Custom Url')
        ));

        if ($this->getRequest()->getParam('rulesid') == '2' || $model->getData('rules_id') == '2') {

            $fieldset->addField($prefix.'min_stock', 'text', array(
                'name'      => $prefix.'min_stock',
                'label'     => Mage::helper('prolabels')->__('Display if Stock is lower then'),
                'title'     => Mage::helper('prolabels')->__('Display if Stock is lower then'),
            ));

            $fieldset->addField($prefix.'out_stock', 'select', array(
                'label'     => Mage::helper('prolabels')->__('Enable Out of stock label'),
                'title'     => Mage::helper('prolabels')->__('Enable Out of stock label'),
                'name'      => $prefix.'out_stock',
                'options'   => array(
                    '1'     => Mage::helper('prolabels')->__('Yes'),
                    '0'      => Mage::helper('prolabels')->__('No'),
                ),
            ));

            $fieldset->addField($prefix.'out_stock_image', 'image', array(
                'name'      => $prefix.'out_stock_image',
                'label'     => Mage::helper('prolabels')->__('Out of stock Image'),
                'title'     => Mage::helper('prolabels')->__('Out of stock Image')
            ));

            $fieldset->addField($prefix.'out_text', 'text', array(
                'name'      => $prefix.'out_text',
                'label'     => Mage::helper('prolabels')->__('Out Of Stock Label Text'),
                'title'     => Mage::helper('prolabels')->__('Out Of Stock Label Text'),
            ));
        }


        $fieldset->addField($prefix.'position_style', 'text', array(
            'name'      => $prefix.'position_style',
            'label'     => Mage::helper('prolabels')->__('Position Style'),
            'title'     => Mage::helper('prolabels')->__('Position Style'),
            'after_element_html' => '<small>Example: top:0px; left:0px;</small>',
        ));

        $fieldset->addField($prefix.'font_style', 'text', array(
            'name'      => $prefix.'font_style',
            'label'     => Mage::helper('prolabels')->__('Font Style'),
            'title'     => Mage::helper('prolabels')->__('Font Style'),
            'after_element_html' => '<small>Example: color: #fff; font: bold 0.9em/11px Arial, Helvetica, sans-serif; letter-spacing: 0.01px;</small>',
        ));

        $fieldset->addField($prefix.'round_method', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Round Method'),
            'title'     => Mage::helper('prolabels')->__('Round Method'),
            'name'      => $prefix.'round_method',
            'options'   => array(
                'round'     => Mage::helper('prolabels')->__('Math'),
                'ceil'      => Mage::helper('prolabels')->__('Ceil'),
                'floor'     => Mage::helper('prolabels')->__('Floor')
            ),
        ));

        $fieldset->addField($prefix.'round', 'text', array(
            'name'      => $prefix.'round',
            'label'     => Mage::helper('prolabels')->__('Round'),
            'title'     => Mage::helper('prolabels')->__('Round'),
            'after_element_html' => '<small>Example: 0.1 or 0.01 or 1 or 10 or 100</small>',
        ));

        if (!$model->getId()) {
            $model->addData(array($prefix.'round' => '1'));
        }

        $form->setValues($model->getData());
        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('prolabels/adminhtml_rules_helper_image')
        );
    }

}
