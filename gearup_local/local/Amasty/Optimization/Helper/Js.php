<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Helper_Js extends Mage_Core_Helper_Abstract
{
    const REGEX_JS_NO_COMMENT = "#<script.*</script>#isU";
    const REGEX_JS            = '#(<!--\[if[^\n]*>\s*(<script.*</script>)+\s*<!\[endif\]-->)|(<script.*</script>)#isU';
    const REGEX_JS_COMBINE    = '#(<!--\[if[^\n]*>\s*(<script.*</script>)+\s*<!\[endif\]-->)|((<!--\[if[^\n]*>)|(<!--))\s*(<(script|link).*((<\/script)|(\/))>)+.*((<!\[endif\]-->)|(-->))|(<script.*</script>)#isU';
    const REGEX_DOCUMENT_END  = '#</body>\s*</html>#isU';

    public function isFooterJsEnabled()
    {
        return Mage::getStoreConfigFlag("amoptimization/footerjs/enabled");
    }

    public function replaceCallback($a)
    {
        if ($this->isIgnored($a[0]) || !$this->isUrlAvailable()) {
            return $a[0];
        }
        
        return '';
    }

    public function isIgnored($html)
    {
        $ignore = Mage::getStoreConfig("amoptimization/footerjs/ignore_list");
        $ignoreList = preg_split('|[\r\n]+|', $ignore, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ignoreList as $ignoreItem){
            preg_match('~/(.*)/~', $ignoreItem, $matches);
            if (false !== strpos($html, $ignoreItem)){
                return true;
            }
            
            if (!empty($matches) && preg_match($matches[0], $html)) {
                return true;
            }
        }
        return false;
    }

    public function removeJs($html)
    {
        $html = preg_replace_callback(
            self::REGEX_JS, array($this, 'replaceCallback'), $html
        );

        return $html;
    }

    /**
     * @return bool
     */
    public function isUrlAvailable()
    {
        $currentPath = $this->getCurrentUrlPath();
        $ignoreUrls = $this->getIgnoredUrls();

        foreach ($ignoreUrls as $url) {
            if (stripos($currentPath, $url) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrlPath()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);

        return $url->getPath();
    }

    /**
     * @return array
     */
    public function getIgnoredUrls()
    {
        $acceptedUrls = explode(PHP_EOL, Mage::getStoreConfig("amoptimization/footerjs/ignore_url"));

        return  array_map('trim', $acceptedUrls);
    }

    public function moveJsToFooter($html)
    {
        if (preg_match_all(self::REGEX_JS_COMBINE, $html, $matches)) {
            $result = $this->removeJs($html);

            $scripts = '';

            foreach ($matches[0] as $key => $match) {
                // cut js from combined comments
                if (isset($matches[3][$key])
                    && !empty($matches[3][$key])
                    && preg_match_all(self::REGEX_JS_NO_COMMENT, $match, $commentedJs)
                ) {
                    // implode cutting js in one comment
                    $match = $matches[3][$key] . implode('', $commentedJs[0]) . $matches[11][$key];
                }
                if (!$this->isIgnored($match) && $this->isUrlAvailable()) {
                    $scripts .= $match;
                }
            }
            $scripts = str_replace('$', '\$', $scripts);

            $result = preg_replace(
                self::REGEX_DOCUMENT_END, "$scripts\\0", $result, -1, $count
            );

            if ($count == 1) {
                return $result;
            }
        }

        return $html;
    }
}
