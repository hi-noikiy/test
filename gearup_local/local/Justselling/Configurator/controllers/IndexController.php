<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_IndexController extends Mage_Core_Controller_Front_Action
{
    /** @var int The time (in minutes) a product will be cached */
    const CACHE_LIFETIME_PRODUCT_MINUTES = 60;

    protected function objectToArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }

        return $d;
    }

    public function refreshAction()
    {
        session_write_close();
        $_raStart = microtime(true);
        $params = $this->getRequest()->getParams();

        if (isset($params['product']))
            $productId = $params['product'];
        if ($productId) {
            $product = $this->loadProduct($productId);
            Mage::register('current_product', $product);
        }
        if (isset($params['skin'])) {
            Mage::getDesign()->setTheme($params['skin']);
        }
        if (isset($params['jstemplateoption'])) {
            $jsTemplateId = $params['jstemplateoption'];
        }

        $productOptionId = null;
        if (isset($params['productoptionid'])) {
            $productOptionId = $params['productoptionid'];
            Mage::register('product_option_id', $productOptionId);
        }

        // Check Dynamic Value and store in Session
        if( isset($params['dynamics']) ) {
            $dynamics = json_decode($params['dynamics']);
            foreach($dynamics as $template) {
                $templateAsArray = $this->objectToArray($template);
                if (is_array($templateAsArray)) {
                    foreach (array_keys($templateAsArray) as $key) {
                        $templateAsArray[$key] = $this->objectToArray($templateAsArray[$key]);
                    }
                    $dynamicsArray[$jsTemplateId] = $templateAsArray;
                    Mage::getSingleton('core/session')->setDynamics($dynamicsArray);
                }
            }
        }


        $productOption = Mage::getModel('catalog/product_option')->load($productOptionId);
        $product = $this->loadProduct($productId);
        $productOption->setProduct($product);

        $this->loadLayout();
        $block = $this->getLayout()->getBlock("root");
        $block->setProductOption($productOption);

        //Js_Log::log('time indexcontroller::setSelectedTemplateOptions before ' . (microtime(true) - $_raStart), "profile", Zend_Log::DEBUG, true);
        $cache_key = "PRODCONF_SELECTEDTEMPLATEOPTIONS_".$productOptionId;

		$isEditProductView = $block->hasDeepLink();

        Mage::register('isInEditMode', $isEditProductView);

        $template_id = $block->getTemplateId();
		if($isEditProductView){
			$selected_template_options = $block->buildSelectedTemplateOptions();
		}else{
			if (Mage::helper("configurator")->readFromCache($cache_key)) {
				$selected_template_options = Mage::helper("configurator")->readFromCache($cache_key);
			} else {
				$selected_template_options = $block->buildSelectedTemplateOptions();
				if ($template_id) {
					Mage::helper("configurator")->writeToCache(
						$selected_template_options,
						$cache_key,
						array("PRODCONF", "PRODCONF_TEMPLATE_".$template_id)
					);
				}
			}
		}

        $block->setSelectedTemplateOptions($selected_template_options);

        //Js_Log::log('time indexcontroller::setSelectedTemplateOptions after ' . (microtime(true) - $_raStart), "profile", Zend_Log::DEBUG, true);

        $block->setJsTemplateOption($jsTemplateId);

        //Js_Log::log('time indexcontroller::render layout before ' . (microtime(true) - $_raStart), "profile", Zend_Log::DEBUG, true);
        $oParamKey = "";
        foreach($params as $key => $param){
            if (strpos($key,'o_') !== false) {
                $oParamKey = $oParamKey .$key.$param;
            }
        }
        $hasDymanics = false;
        if(is_array($dynamics) && sizeof($dynamics) > 0 && $dynamics[0] != false){
            $hasDymanics = true;
        }
        $cache_keyHtml = "PRODCONF_TEMPLATE_HTML_T_".$template_id ."_". $oParamKey;
        //Js_Log::log("html cache key=".$cache_keyHtml, $this);
        $html = Mage::helper("configurator")->readFromCache($cache_keyHtml);
        if($html && !$hasDymanics && $template_id){
            $this->getResponse()->appendBody($html);
            //Js_Log::log("html cache hit", $this);
        }else{
            //Js_Log::log("html cache miss", $this);
            $html = $this->renderLayout();
            $lifeTime = intval(trim(Mage::getStoreConfig('productconfigurator/cache/lifetime')));
            Mage::helper("configurator")->writeToCache($html->getResponse()->getBody(), $cache_keyHtml, array("PRODCONF", "PRODCONF_TEMPLATE_".$template_id), true, $lifeTime);
        }
        //Js_Log::log('time indexcontroller::render layout after ' . (microtime(true) - $_raStart), "profile", Zend_Log::DEBUG, true);
    }

    /**
     * Loads the product. If available, it will be returned from cache.
     *
     * @param int $productId
     * @return Mage_Catalog_Model_Product
     */
    private function loadProduct($productId) {
        $cacheKey = 'PRODCONF_PRODUCT_'.$productId;
        $productSerialized = Mage::helper("configurator")->readFromCache($cacheKey);
        if (!$productSerialized) {
            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::helper("configurator")->writeToCache(
                $product,
                $cacheKey,
                array('PRODUCT','PRODCONF_PRODUCT_'.$productId)
            );
            return $product;
        } else {
            return $productSerialized;
        }
    }

    public function deleteuploadAction()
    {
        //Mage::Log("IndexController deleteUploadAction");
        $session_id = Mage::getSingleton('core/session')->getSessionId();
        //Mage::Log("session=".$session_id);

        $id = $this->getRequest()->getParam("id");

        //Mage::Log("id=".$id);
        if ($id) {
            $upload = Mage::getModel('configurator/upload')->load($id);
            if ($upload->getSessionId() == $session_id) {
                Mage::Log("session is ok");
                $upload->delete();
            }
        }
    }
}