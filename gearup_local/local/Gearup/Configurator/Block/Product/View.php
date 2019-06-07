<?php

class Gearup_Configurator_Block_Product_View extends Mage_Catalog_Block_Product_View {

    public function getJsonConfig() {
         $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();

        /** @var Mage_Catalog_Helper_Product_Type_Composite $compositeProductHelper */
        $compositeProductHelper = $this->helper('catalog/product_type_composite');
        $config = array_merge(
            $compositeProductHelper->prepareJsonGeneralConfig(),
            $compositeProductHelper->prepareJsonProductConfig($product)
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        if(isset($config['priceFormat']['pattern'])){
          $pattern =  explode('%s',$config['priceFormat']['pattern']);         
          $config['priceFormat']['pattern'] = '%s' .' '. $pattern[1];
        }
        return Mage::helper('core')->jsonEncode($config);
    }

}
