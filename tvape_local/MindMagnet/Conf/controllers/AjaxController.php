<?php

require_once(Mage::getModuleDir('controllers','Amasty_Conf').DS.'AjaxController.php');

class MindMagnet_Conf_AjaxController extends Amasty_Conf_AjaxController
{

    /**
     * Action overwritten in order to get functionality in current theme
     */
    public function indexAction()
    {
        parent::indexAction();
        
        $configurableId = Mage::helper('mindmagnetconf')->getConfigurableId();
                
        $pr = Mage::registry('product');

        if (isset($pr) && ($pr->getId())) {
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($pr->getId());
            
            if (count($parentIds) > 1) {
                if (in_array($configurableId, $parentIds)) {
                    foreach ($parentIds as $p) {
                        if ($p == $configurableId) {
                            $configProduct = Mage::getModel('catalog/product')
                                    ->setStoreId(Mage::app()->getStore()->getId())
                                    ->load($configurableId);
                            break;
                        }
                    } 
                    
                }
            } else {
                $configProduct = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($parentIds);
            }

            $productDesignConfig = $configProduct->getCustomDesign();

            $productDesignConfigExploded = explode('/', $productDesignConfig);


            /** Hardcoded theme in order to keep this functionality only in tvtheme-v2 */
            if (isset($productDesignConfigExploded[1]) && $productDesignConfigExploded[1] && $productDesignConfigExploded[1] == 'tvtheme-v2') {

                Mage::getSingleton('core/design_package')
                    ->setPackageName('ultimo')
                    ->setTheme('tvtheme-v2');

                    $template = 'catalog/product/view/media.phtml';

                    $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('catalog/product_view_media', 'product.info.media')->setTemplate($template)->toHtml()
                    );

            }
        }
    }

}
