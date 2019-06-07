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



abstract class Mirasvit_Fpc_Model_Blockmf_Abstract extends Varien_Object
{
    const CONTAINER_ID_PREFIX = 'FPC_CONTAINER';
    const EMPTY_VALUE = 'FPC_EMPTY_BLOCK';

    /**
     * @var string
     */
    protected $_hash = null;

    /**
     * @var string
     */
    protected $_cacheId = null;

    /**
     * @param array $definition
     * @param object $block
     */
    public function __construct($definition, $block)
    {
        ini_set('pcre.backtrack_limit', 100000000);

        $this->addData($definition)
            ->setNameInLayout($block->getNameInLayout())
            ->setLayoutXml(Mage::helper('fpc/layout')->generateBlockLayoutXML($block->getNameInLayout()))
            ;
    }

    /**
     * @param string $html
     * @return string
     */
    public function getWrappedHtml($html)
    {
        return $this->_getStartWrapperTag().$html.$this->_getEndWrapperTag();
    }

    /**
     * @return string
     */
    protected function _getStartWrapperTag()
    {
        return '<fpc definition="'.$this->getDefinitionHash().'">';
    }

    /**
     * @return string
     */
    protected function _getEndWrapperTag()
    {
        return '</fpc definition="'.$this->getDefinitionHash().'">';
    }

    /**
     * @return string
     */
    public function getDefinitionHash()
    {
        if ($this->_hash == null) {
            $this->_hash = md5($this->getBlockType().'|'.$this->getNameInLayout());
        }

        return $this->_hash;
    }

    /**
     * @param string $content
     * @return void
     */
    public function saveToCache($content)
    {
        $pattern = '/'.preg_quote($this->_getStartWrapperTag(), '/').'(.*?)'.preg_quote($this->_getEndWrapperTag(), '/').'/ims';
        ini_set('pcre.backtrack_limit', 100000000);

        $matches = array();

        preg_match($pattern, $content, $matches);

        if (isset($matches[1])) {
            $this->saveCache($matches[1]);
        } else {
            $this->saveCache(self::EMPTY_VALUE);
        }

        return $this;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function applyToContentWithoutApp(&$content)
    {
        $fromCache = 1;
        $startTime = microtime(true);

        $pattern = '/'.preg_quote($this->_getStartWrapperTag(), '/').'(.*?)'.preg_quote($this->_getEndWrapperTag(), '/').'/ims';

        $html = false;

        $blockKey = $this->getBlockType().'|'.$this->getNameInLayout();

        switch ($this->getBehavior()) {
            case 'ajax':
                $tagId = 'fpcblock'.$this->getDefinitionHash();
                $html = '<div id="'.$tagId.'">'.$this->loadCache().'</div>';
                $html .= '<script>
                    if(fpcBlocks === undefined)
                        var fpcBlocks = {};
                    fpcBlocks["'.$tagId.'"] = "'.$blockKey.'";
                    </script>';
                break;

            default:
                $html = $this->loadCache();
                break;
        }

        if ($html !== false) {
            Mirasvit_Fpc_Helper_Fpcmf_Debugmf::appendDebugInformationToBlock($html, $this, 1, microtime(true));

            $content = preg_replace($pattern, str_replace('$', '\\$', $html), $content, 1);

            $this->setRenderTime(microtime(true) - $startTime);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $content
     * @return void
     */
    public function applyToContentInApp(&$content)
    {
        $startTime = microtime(true);

        $layoutHelper = Mage::helper('fpc/fpcmf_layoutmf');

        $html = $layoutHelper->renderBlockHtml($this->getLayoutXml(), $this->getNameInLayout());

        $html = $this->getWrappedHtml($html);

        $pattern = '/'.preg_quote($this->_getStartWrapperTag(), '/').'(.*?)'.preg_quote($this->_getEndWrapperTag(), '/').'/ims';

        $fromCache = ($this->getBehavior() == 'holepunch') ? 0 :1;

        Mirasvit_Fpc_Helper_Fpcmf_Debugmf::appendDebugInformationToBlock($html, $this, $fromCache, microtime(true));

        $content = preg_replace($pattern, str_replace('$', '\\$', $html), $content, 1);

        $this->saveToCache($content);

        $this->setRenderTime(microtime(true) - $startTime);
    }

    /**
     * @return string
     */
    public function getCacheId()
    {
        if ($this->_cacheId === null) {
            $sessionHelper = new Mirasvit_Fpc_Helper_Fpcmf_Sessionmf();

            $dependencies = false;
            //check mobile detect
            $mobileHelper = new Mirasvit_Fpc_Helper_Fpcmf_Mobilemf();
            $deviceType = $mobileHelper->getMobileDeviceType();
            foreach ($this->getDependencies() as $dependence) {
                $dependencies .= $dependence.'='.$sessionHelper->get($dependence, true);
            }
            $dependencies .= $deviceType;

            //User Agent Segmentation
            $userAgent = (isset($_SERVER['HTTP_USER_AGENT'])
                && $_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            if ($userAgent) {
                $config = new Mirasvit_Fpc_Model_Configmf();
                $userAgentSegmentation = $config->getUserAgentSegmentation();
                foreach ($userAgentSegmentation as $segment) {
                    if ($segment['useragent_regexp']
                        && preg_match($segment['useragent_regexp'], $userAgent)) {
                            $dependencies .= $segment['cache_group'];
                    }
                }
            }

            if ($dependencies) {
                $this->_cacheId = self::CONTAINER_ID_PREFIX.'_'.md5($this->getDefinitionHash().$dependencies);
            } else {
                $this->_cacheId = false;
            }
        }

        return $this->_cacheId;
    }

    /**
     * @param string $blockContent
     * @return void
     */
    public function saveCache($blockContent)
    {
        $cacheId = $this->getCacheId();

        // echo 'save '.$this->getNameInLayout().': '.$cacheId.'<br>';

        if ($cacheId !== false) {
            $tags = array(Mirasvit_Fpc_Model_Configmf::CACHE_TAG);
            Mirasvit_Fpc_Model_Cachemf::getCacheInstance()->save($blockContent, $cacheId, $tags, $this->getLifetime());
        }

        return $this;
    }

    /**
     * @return bool|string
     */
    public function loadCache()
    {
        $cacheId = $this->getCacheId();

        // echo 'load '.$this->getNameInLayout().': '.$cacheId.'<br>';

        if ($cacheId === false) {
            return false;
        }

        return Mirasvit_Fpc_Model_Cachemf::getCacheInstance()->load($cacheId);
    }
}
