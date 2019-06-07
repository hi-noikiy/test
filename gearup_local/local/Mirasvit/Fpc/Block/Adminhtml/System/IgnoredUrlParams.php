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
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Block_Adminhtml_System_IgnoredUrlParams extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('url_params', array(
            'label' => __('Ignored url parameters'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::__construct();
    }


    /**
     * Get the grid and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        // Workaround for Magento bug within 1.9.3.x
        if (!$this->getElement()->getData('value')) {
            $this->getElement()->setData('value', unserialize(Mage::getStoreConfig('fpc/cache_rules/ignored_url_params')));
        }
        $html = $this->_toHtml();
        $this->_arrayRowsCache = null; // doh, the object is used as singleton!
        return $html;
    }
}
