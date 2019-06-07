<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Observer_Minification
{
    const CONTENT_TYPE_HEADER = 'Content-Type';
    
    const HTML_TYPE = 'text/html';

    protected $_additionalFiles = array(
        'js' => array(
            'js/scriptaculous/builder.js',
            'js/scriptaculous/effects.js',
            'js/scriptaculous/dragdrop.js',
            'js/scriptaculous/controls.js',
            'js/scriptaculous/slider.js',
            'js/scriptaculous/sound.js'
        )
    );
    
    public function onControllerResponseSendBefore($observer)
    {
        $response = Mage::app()->getResponse();
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] == self::CONTENT_TYPE_HEADER
                && strpos($header['value'], self::HTML_TYPE) === false
            ) {
                return;
            }
        }

        $page = $response->getBody();
        $responseModified = false;

        $processors = array('js', 'css', 'fingerprints', 'footerjs', 'html');

        foreach ($processors as $code) {
            if (!Mage::getStoreConfigFlag("amoptimization/$code/enabled"))
                continue;

            /** @var Amasty_Optimization_Model_Minification_Processor $processor */
            $processor = Mage::getSingleton("amoptimization/minification_processor_$code");

            $page = $processor->process($page);

            if (isset($this->_additionalFiles[$code])) {
                foreach ($this->_additionalFiles[$code] as $additionalFile) {
                    $processor->getMinificator()->minify($additionalFile);
                }
            }

            $responseModified = true;
        }

        if ($responseModified) {
            $response->setBody($page);
        }
    }
}
