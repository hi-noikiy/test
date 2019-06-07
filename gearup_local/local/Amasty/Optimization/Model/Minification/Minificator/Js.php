<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Minificator_Js extends Amasty_Optimization_Model_Minification_Minificator
{
    const MINIFICATOR_URL = 'https://closure-compiler.appspot.com/compile';

    public function isDeferred()
    {
        return true;
    }

    public function getCode()
    {
        return 'js';
    }

    public function minify($path)
    {
        $ignoreMin = Mage::getStoreConfigFlag('amoptimization/js/ignore_min');

        if ($ignoreMin && substr($path, -7) == '.min.js')
            return $path;
            
        if (strpos($path, 'tiny_mce.js') !== false) {
            return $path;// this file makes a lot of 404 errors for other scripts
        }

        return parent::minify($path);
    }

    protected function minifyFile($path)
    {
        if (!function_exists('curl_init')) {
            if (Mage::getStoreConfigFlag('amoptimization/debug/log_minification_errors')) {
                Mage::log(
                    "Minification failed. Curl module is not installed'.",
                    Zend_Log::WARN,
                    '',
                    true
                );
            }

            return false;
        }

        $level = Mage::getStoreConfig('amoptimization/js/level');

        if (!$level) {
            $level = 'WHITESPACE_ONLY';
        }

        if (Mage::getStoreConfig('amoptimization/js/send_type')
            == Amasty_Optimization_Model_Config_Source_Sent_Options::SENT_URL
        ) {
            $separator = PHP_OS == 'Windows' ? '\\' : DS;
            $sentContent = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
                . str_replace(Mage::getBaseDir() . $separator, '', $path);
            $sentType = 'code_url';
        } else {
            $sentType = 'js_code';
            $sentContent = file_get_contents($path);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::MINIFICATOR_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch, CURLOPT_POSTFIELDS,
            http_build_query(array(
                $sentType => $sentContent,
                'compilation_level' => $level,
                'output_format' => 'text',
                'output_info' => 'compiled_code',
                'language_out' => 'ES5',
            ))
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($responseCode == 200 && substr($serverOutput, 0, 5) != 'Error' && strlen($serverOutput) > 2) {
            $serverOutput = $this->_processGoogleOutput($serverOutput);
            file_put_contents($path, $serverOutput, LOCK_EX);

            return true;
        }
        else {
            if ($responseCode != 200) {
                $message = "Minification failed. Minification service returned code $responseCode'.";
            }
            else {
                $serverOutput = trim($serverOutput);
                $message = "Minification failed. Server response contains the following message: '$serverOutput'.";
            }

            if (Mage::getStoreConfigFlag('amoptimization/debug/log_minification_errors')) {
                //Mage::log($message, Zend_Log::WARN, '', true);
            }

            return false;
        }
    }

    /**
     * @param string $content
     * @return string
     */
    protected function _processGoogleOutput($content)
    {
        $content = preg_replace('@\$jscomp.*?;@s', '', $content);
        $content = preg_replace('@\A\/\*.*?\*\/\n@s', '', $content, 1);

        return $content;
    }
}
