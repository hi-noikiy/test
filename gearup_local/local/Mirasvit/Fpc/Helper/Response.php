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



class Mirasvit_Fpc_Helper_Response extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $content
     * @return void
     */
    public function cleanExtraMarkup(&$content, $isSid = true)
    {
        $content = preg_replace('/<\[!--\{(.*?)\}--\]>/', '', $content);
        $content = preg_replace('/<\[!--\/\{(.*?)\}--\]>/', '', $content);
        if ($isSid) {
            $sid = array('___SID=U&amp;','___SID=U&','?___SID=U');
            $content = str_replace($sid, '', $content);
        }
    }

    /**
     * @param string $content
     * @return void
     */
    public function updateFormKey(&$content)
    {
        if ($formKey = Mage::getSingleton('core/session')->getFormKey()) {
            $content = preg_replace(
                '/<input type="hidden" name="form_key" value="(.*?)" \\/>/i',
                '<input type="hidden" name="form_key" value="' . $formKey . '" />',
                $content
            );

            $content = preg_replace(
                '/name="form_key" type="hidden" value="(.*?)" \\/>/i',
                'name="form_key" type="hidden" value="' . $formKey . '" />',
                $content
            );

            $content = preg_replace(
                '/\\/form_key\\/([^\"\'\/\s])+(\/|\"|\')/i',
                '/form_key/' . $formKey . "$2",
                $content
            );

            $content = preg_replace(
                '/\\/form_key' . '\\\\' . '\\/(.*?)' . '\\\\' . '\\//i',
                '/form_key\/' . $formKey . '\/',
                $content
            );
        }
    }

    /**
     * @param string $content
     * @return void
     */
    public function updateWelcomeMessage(&$content)
    {
        $welcome = false;

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $welcome = Mage::helper('fpc')->__('Welcome, %s!', Mage::helper('core')->escapeHtml(Mage::getSingleton('customer/session')->getCustomer()->getName()));
        }

        if ($welcome) {
            $content = preg_replace(
                '/\\<p class="welcome-msg"\\>(.*?)\\<\\/p\\>/i',
                '<p class="welcome-msg">' . $welcome .'</p>',
                $content,
                1
            );

            $content = preg_replace(
                '/\\<div class="welcome-msg"\\>(.*?)\\<\\/div\\>/i',
                '<div class="welcome-msg">' . $welcome .'</div>',
                $content,
                1
            );

            $content = preg_replace(
                '/\\<span class="welcome-msg"\\>(.*?)\\<\\/span\\>/i',
                '<span class="welcome-msg">' . $welcome .'</span>',
                $content,
                1
            );
        }
    }

    /**
     * @param string $content
     * @return void
     */
    public function updateZopimInfo(&$content)
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()
            && Mage::helper('mstcore')->isModuleInstalled('Diglin_Chat')) {
                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    $customerName = $customer->getName();
                    $customerEmail = $customer->getEmail();

                    $content = preg_replace(
                        '/\\$zopim\\.livechat\\.setName\\(\'(.*?)\'\\)\;/i',
                        '$zopim.livechat.setName(\'' . $customerName . '\');',
                        $content,
                        1
                    );

                    $content = preg_replace(
                        '/\\$zopim\\.livechat\\.setEmail\\(\'(.*?)\'\\)\;/i',
                        '$zopim.livechat.setEmail(\'' . $customerEmail . '\');',
                        $content,
                        1
                    );

        }
    }

    /**
     * window.history.replaceState can add incorrect redirect for urls with Ignored Url Parameters
     *
     * @param string $content
     * @return void
     */
    public function deleteWrongData(&$content)
    {
        $content = preg_replace(
            '/window\\.history\\.replaceState\\((.*?)\\);/i',
            '',
            $content
        );
    }

    /**
     * @param string $content
     * @return void
     */
    public function updateIgnoredUrlParams(&$content)
    {
        $debug = Mage::helper('fpc/debug');
        $debug->startTimer('FPC_UPDATE_IGNORED_URL_PARAMS');
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $content, $match); //get all page urls
        $ignoredUrlParams = Mage::getSingleton('fpc/config')->getIgnoredUrlParams();
        $urlsWithIgnoredUrlParams = array();
        $urlsForReplace = array();
        if (isset($match[0]) && $match[0] && $ignoredUrlParams && count($match[0]) < 2000) {
            foreach ($match[0] as $url) {
                if (preg_match('/'. implode('|',  $ignoredUrlParams) .'/ims', $url)) {
                    $urlsWithIgnoredUrlParams[] = $url;
                    $urlsForReplace[] = $this->_prepareUrl($url,$ignoredUrlParams);
                }
            }
        }
        if ($urlsWithIgnoredUrlParams && $urlsForReplace) {
            $content =  str_replace($urlsWithIgnoredUrlParams, $urlsForReplace, $content);
        }
        $debug->stopTimer('FPC_UPDATE_IGNORED_URL_PARAMS');
    }

    protected function _prepareUrl($url, $ignoredUrlParams)
    {
        $url = str_replace('&amp;', '&', $url);
        $urlParsed = explode('?',$url);
        if (isset($urlParsed[1]) && ($urlParsedParams = explode('&', $urlParsed[1])) ) {
            foreach ($urlParsedParams as $key => $param) {
                if (preg_match('/'. implode('|',  $ignoredUrlParams) .'/ims', $param)
                    && ($preparedParam = trim(strtok($param, '=')))
                    && !isset($_GET[$preparedParam])
                ) {
                        unset($urlParsedParams[$key]);
                }
            }

            if ($urlParsedParams) {
                $url = $urlParsed[0] . '?' . implode('&', $urlParsedParams);
            } else {
                $url = $urlParsed[0];
            }
        }

        return $url;
    }
}