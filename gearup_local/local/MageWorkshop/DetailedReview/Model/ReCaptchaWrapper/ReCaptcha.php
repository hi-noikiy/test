<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Class MageWorkshop_DetailedReview_Model_ReCaptchaWrapper_ReCaptcha
 */
class MageWorkshop_DetailedReview_Model_ReCaptchaWrapper_ReCaptcha extends Mage_Core_Model_Abstract
{
    const SITE_VERIFY_URL = "https://www.google.com/recaptcha/api/siteverify?fallback=true&";
    const VERSION         = "php_1.0";
    
    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param array  $data array of parameters to be sent.
     *
     * @return array response
     */
    protected function _submitHttpGet($data)
    {
        /** @var Mage_Api2_Model_Renderer_Query $madel */
        $api2RendererQueryModel = Mage::getModel('api2/renderer_query');
        $req = $api2RendererQueryModel->render($data);
        
        $curlAdapter = new Mage_HTTP_Client_Curl();
        try {
            $curlAdapter->get(self::SITE_VERIFY_URL . $req);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $curlAdapter->getBody();
    }
    
    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $response response string from recaptcha verification.
     * @param string $reCaptchaPrivateKey ReCaptcha private key configuration
     * @return array
     */
    public function verifyResponse($response, $reCaptchaPrivateKey)
    {
        $getResponse = $this->_submitHttpGet(
            array (
                'secret'   => $reCaptchaPrivateKey,
                'remoteip' => Mage::app()->getRequest()->getServer('REMOTE_ADDR'),
                'v'        => self::VERSION,
                'response' => $response
            )
        );
        
        return $parsedJson = json_decode($getResponse, true);
    }
}