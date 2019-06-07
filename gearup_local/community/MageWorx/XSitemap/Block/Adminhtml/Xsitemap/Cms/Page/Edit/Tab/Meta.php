<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Cms_Page_Edit_Tab_Meta extends MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Cms_Page_Edit_Tab_Meta_Abstract
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form     = $this->getForm();
        $fieldset = $form->getElements()->searchById('meta_fieldset');

        $values = Mage::getModel('eav/entity_attribute_source_boolean')->getAllOptions();
        $fieldset->addField('exclude_from_sitemap', 'select',
            array(
            'name'   => 'exclude_from_sitemap',
            'label'  => Mage::helper('xsitemap')->__('Exclude from XML Sitemap'),
            'values' => $values,
        ));

        $model = Mage::registry('cms_page');
        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }

}
