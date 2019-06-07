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

class Justselling_Configurator_CombinedimageController extends Mage_Core_Controller_Front_Action
{

    public function refreshAction()
    {
        //session_write_close();
        $params = $this->getRequest()->getParams();

        $product = null;
        $productId = $params['product'];
        if ($productId) {
            $product = Mage::getModel("catalog/product")->load($productId);
            Mage::register('current_product', $product);
        }

        $js_template_option = null;
        if (isset($params['jstemplateoption'])) {
            $js_template_option = $params['jstemplateoption'];
        }

        $productOptionId = null;
        if (isset($params['productoptionid'])) {
            $productOptionId = $params['productoptionid'];
            $productOption = Mage::getModel('catalog/product_option')->load($productOptionId);
        }

        // Check Dynamic Value and store in Session
        if( isset($params['dynamics']) ) {
            foreach($params['dynamics'] as $templateId => $template) {
                Mage::getSingleton('core/session')->setDynamics($template);
            }
        }

        $templateOptions = array();
        if( isset($params['options']) ) {
            foreach($params['options'] as $optionId => $option) {
                $keys = array_keys($option);
                if (is_array($option) && ((string)$keys[0] == (string)$params['jstemplateoption'])) {
                    foreach($option as $configId => $config) {
                        if ($configId == $params['jstemplateoption']) {
                            if (is_array($option)) {
                                if( isset($config['template']) ) {
                                    $productOptionId = $optionId;
                                    foreach($config['template'] as $templateOptionId => $templateOptionValue) {
                                        $templateOptions[$templateOptionId] = $templateOptionValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $templateId = Mage::getModel('configurator/template')->getLinkedTemplateId($productOptionId);
        $template = Mage::getModel('configurator/template')->load($templateId);

        $body = "";
        if ($template->getCombinedProductImage()) {
            $body = Mage::helper('configurator/Combinedimage')->getCombinedProductImage(
                $product,
                $template,
                $templateOptions,
                $js_template_option
            );
        }

        $this->getResponse()->setHeader('Content-type', 'application/html');
        $this->getResponse()->setBody($body);


    }

    protected function checkUploadFolder() {
        $mediaFolder = Mage::getBaseDir('media');
        $configuratorFolder = $mediaFolder . DS . "configurator";
        if (!file_exists($configuratorFolder)) {
            mkdir($configuratorFolder);
        }
        $uploadFolder = $configuratorFolder . DS . "uploads";
        if (!file_exists($uploadFolder)) {
            mkdir($uploadFolder);
        }
        return $uploadFolder;
    }

    public function uploadAction() {
        session_write_close();
        $params = $this->getRequest()->getParams();

        if (isset($params['optionid']) && isset($params['jstemplateid']) && isset($params['imgData'])) {
            $optionId = $params['optionid'];
            $jsTemplateId = $params['jstemplateid'];

            $imgData = $params['imgData'];
            $imgData = str_replace('data:image/png;base64,', '', $imgData);
            $imgData = str_replace(' ', '+', $imgData);
            $imgData = base64_decode($imgData);

            $folder = $this->checkUploadFolder();

            $filename = "combinedimage_".$optionId."_".$jsTemplateId.".png";
            file_put_contents($folder . DS . $filename, $imgData);
        }
    }

    public function getTransparentImageAction() {
        $params = $this->getRequest()->getParams();

        if (isset($params['width']) && isset($params['height'])) {
            $width = $params['width'];
            $height = $params['height'];

            if(isset($params['color'])){
                $color = $params['color'];
                $image = imagecreate($width, $height);
                $hexcode = '#'.$color;
                $rgb = Mage::helper('configurator/combinedimage')->hex2rgb($hexcode);
                $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
            }else{
                $image = imagecreatetruecolor($width, $height);
                imagesavealpha($image, true);
                $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
            }
            imagefill($image, 0, 0, $color);

            ob_start ();
            imagepng($image);
            $data = ob_get_contents();
            ob_end_clean ();

            imagedestroy($image);

            $this->getResponse()->setHeader('Content-type', 'image/png');
            $this->getResponse()->setHeader('Cache-Control', 'private, max-age=10800, pre-check=10800');
            $this->getResponse()->setHeader('Pragma','private');
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822,strtotime(" 2 day")));
            $this->getResponse()->setBody($data);
        }
    }
}


