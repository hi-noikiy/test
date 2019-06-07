<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoBase_Model_Observer_Category extends Mage_Core_Model_Abstract
{
    /**
     * Retrive options for canonical URL attribute
     * @param type $observer
     */
    public function modifyFormFieldForProductAttributes($observer)
    {
        //adminhtml_catalog_category_edit_prepare_form
        $form = $observer->getForm();

        $metaRobots = $form->getElement('meta_robots');

        if($metaRobots){
            $metaRobots->setValues(Mage::getModel('seosuite/catalog_product_attribute_source_meta_robots')->getAllOptions());
        }
    }
}