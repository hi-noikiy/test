<?php
/**
 * @author Amasty Team
 * @copyright Amasty
 * @package Amasty_Fpc
 */

class Amasty_Fpc_Model_Fpc_Front
{
    protected static $_configCache = null;
    protected static $_additionalConfigs = array(
        'web/url/use_store',
        'web/cookie/cookie_restriction'
    );

    protected static $_cookieUpdated = false;

    protected $_debug = null;
    protected $_cookieDebugTrigger = false;
    protected $_debugInfo = null;
    protected $_dynamicBlocks = null;

    protected static $_currentCacheKey = null;

    protected static $_storeCode = null;
    protected static $_isMobile = null;

    protected $_cache = null;
    protected $_sessionName = 'frontend';
    protected $_sessionStarted = false;

    protected $_formKey = null;

    protected $_coreInitTime = 0.015; // Used to measure time on PHP<5.4

    const PAGE_LOAD_HIT = 1;
    const PAGE_LOAD_MISS = 2;
    const PAGE_LOAD_NEVER_CACHE = 3;
    const PAGE_LOAD_IGNORE = 4;
    const PAGE_LOAD_HIT_UPDATE = 5;
    const PAGE_LOAD_HIT_SESSION = 6;
    const PAGE_LOAD_IGNORE_PARAM = 7;
    const PAGE_LOAD_CMS_UPDATE = 8;

    public function __construct()
    {
        if (isset($_SESSION)) {
            $this->_sessionName = session_name();
        }
        else
        {
            $sessionName = (string)Mage::app()
                ->getConfig()
                ->getNode('global/amfpc/session_name');

            if ($sessionName) {
                $this->_sessionName = $sessionName;
            }
        }
    }

    public function getCache()
    {
        if (!$this->_cache)
            $this->_cache = new Amasty_Fpc_Model_Fpc();

        return $this->_cache;
    }

    public static function getStoreCode()
    {
        if (self::$_storeCode === null)
        {
            $store = isset($_COOKIE['store']) ? $_COOKIE['store'] : false;

            if (isset($_GET['___store']))
            {
                $code = $_GET['___store'];
            }
            else if (self::getDbConfig('web/url/use_store'))
            {
                $request = new Zend_Controller_Request_Http();

                $baseUrl = strtok($request->getBaseUrl(), '?');
                $pathInfo = substr($request->getRequestUri(), strlen($baseUrl));
                $pathParts = explode('/', ltrim($pathInfo, '/'), 2);

                $code = $pathParts[0];
            }

            if (isset($code) && $code)
            {
                $resource = Mage::getSingleton('core/resource');
                $adapter = $resource->getConnection('core_read');

                $select = $adapter->select()
                    ->from(array('store' => $resource->getTableName('core/store')), 'IF(group.group_id, 1, 0)')
                    ->joinLeft(
                        array('group' => $resource->getTableName('core/store_group')),
                        'group.group_id = store.group_id AND group.default_store_id = store.store_id',
                        array()
                    )
                    ->where('store.code = ?', $code)
                ;

                $result = $adapter->fetchOne($select);

                if ($result !== false)
                {
                    if ($result) // Default store
                        $store = false;
                    else
                        $store = $code;
                }
            }

            self::$_storeCode = $store;
        }

        return self::$_storeCode;
    }

    public static function getCustomerGroupId()
    {
        if (self::getDbConfig('amfpc/general/no_groups'))
            return false;

        if (isset($_SESSION))
        {
            foreach ($_SESSION as $key => $section)
            {
                if (preg_match('/customer(_\w+)?/', $key))
                {
                    if (isset($section['customer_group_id']))
                        return $section['customer_group_id'];
                }
            }
        }

        return Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
    }

    public static function removeDisregardedParams($getParams)
    {
        if ($paramsString = self::getDbConfig('amfpc/pages/disregard_params'))
        {
            $params = preg_split('/[,\s]+/', $paramsString, -1, PREG_SPLIT_NO_EMPTY);

            $disregarded = array_intersect_key(array_flip($params), $getParams);

            foreach ($disregarded as $name => $value)
                unset($getParams[$name]);
        }

        ksort($getParams);

        return $getParams;
    }

    public static function getFullUrl()
    {
        $protocol = self::getSecureKey() ? 'https' : 'http';
        $url = "$protocol://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        return $url;
    }

    public static function getSecureKey()
    {
        return (int)(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    }

    public static function getCacheKey()
    {
        if (self::$_currentCacheKey === null)
        {
            $mobile = self::isMobile() ? 'm' : false;
            $currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : false;
            $store = self::getStoreCode();
            $customerGroup = self::getCustomerGroupId();

            $url = strtok($_SERVER['REQUEST_URI'], '?');
            $params = self::removeDisregardedParams($_GET);

            $secure = self::getSecureKey();

            $key = 'amfpc_' . $mobile . $currency . $store . $customerGroup
                . ($secure ? 'https://' : 'http://'). $_SERVER['HTTP_HOST']
                . $url
            ;

            if (!empty($params)) {
                $queryString = '?' . http_build_query($params);

                $key .= $queryString;
            }

            self::$_currentCacheKey = sha1($key);
        }

        return self::$_currentCacheKey;
    }

    protected function getSessionSaveMethod()
    {
        return (string)Mage::app()->getConfig()->getNode('global/session_save');
    }

    protected function getSessionSavePath()
    {
        if ($sessionSavePath = Mage::app()->getConfig()->getNode('global/session_save_path'))
        {
            return $sessionSavePath;
        }

        return Mage::getBaseDir('session');
    }

    protected function isAdmin()
    {
        if (preg_match('|/key/\w{32,}/|', $_SERVER['REQUEST_URI']))
            return true;

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/adminhtml_"))
            return true;

        $config = Mage::app()->getConfig();

        $adminKey = (string)$config->getNode('admin/routers/adminhtml/args/frontName');

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/$adminKey"))
            return true;
    }

    protected function startSession()
    {
        if (isset($_SESSION))
            return true;

        $moduleName = $this->getSessionSaveMethod();
        switch ($moduleName) {
            case 'db':
                $moduleName = 'user';
                if ($this->isRedisEnabled())
                    $sessionResource = new Amasty_Fpc_Model_Resource_Redis_Session();
                else
                    $sessionResource = new Amasty_Fpc_Model_Resource_Session();

                $sessionResource->setSaveHandler();
                break;
            case 'user':
                call_user_func($this->getSessionSavePath());
                break;
            case 'files':
                if (!is_writable($this->getSessionSavePath())) {
                    break;
                }
            default:
                session_save_path($this->getSessionSavePath());
                break;
        }
        session_module_name($moduleName);

        session_name($this->_sessionName);

        session_start();

        if ($moduleName == 'files')
            $this->_sessionStarted = true;

        return true;
    }

    protected function isRedisEnabled()
    {
        $fileConfig = new Mage_Core_Model_Config_Base();
        $fileConfig->loadFile(Mage::getBaseDir('etc') . DS . 'modules' . DS . 'Cm_RedisSession.xml');

        $isActive = $fileConfig->getNode('modules/Cm_RedisSession/active');

        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }

        return true;
    }

    protected function closeSession()
    {
        if ($this->_sessionStarted)
        {
            $_SESSION = null;
            session_write_close();
        }
    }

    protected function hasMessages()
    {
        if (isset($_SESSION))
        {
            foreach ($_SESSION as $section)
            {
                if (isset($section['messages']) && $section['messages'] instanceof Mage_Core_Model_Message_Collection)
                {
                    if ($section['messages']->count() > 0)
                        return true;
                }
            }
        }
        return false;
    }

    protected function checkSession()
    {
        $result = (isset($_SESSION['core']['visitor_data']['customer_id'])
            ||
            isset($_SESSION['customer_base']['id'])
            ||
            isset($_SESSION['core']['visitor_data']['quote_id'])
            ||
            isset($_SESSION['checkout']['last_added_product_id'])
            ||
            isset($_SESSION['checkout']['cart_was_updated'])
            ||
            isset($_SESSION['checkout']['checkout_state'])
            ||
            $this->hasMessages()
        );

        return $result;
    }

    protected function containsIgnoredParams()
    {
        if ($paramsString = $this->getDbConfig('amfpc/pages/ignored_params'))
        {
            $params = preg_split('/[,\s]+/', $paramsString, -1, PREG_SPLIT_NO_EMPTY);

            if (!empty($params))
            {
                foreach ($params as $param)
                {
                    if (isset($_GET[$param]))
                    {
                        Mage::register('amfpc_ignored', self::PAGE_LOAD_IGNORE_PARAM, true);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function ignore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET')
            return true;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return true;

        if ($this->isAdmin())
            return true;

        if ($this->containsIgnoredParams())
            return true;

        if ($this->inIgnoreList())
        {
            Mage::register('amfpc_ignorelist', true, true);
            return true;
        }

        if (!$this->isDynamicBlocksEnabled())
        {
            if (isset($_COOKIE[Mage_Persistent_Model_Session::COOKIE_NAME]))
                return true;
        }

        if (!isset($_COOKIE[$this->_sessionName]))
            return false;

        $this->startSession();

        $this->_formKey = isset($_SESSION['core']['_form_key']) ? $_SESSION['core']['_form_key'] : false;

        if (!$this->isDynamicBlocksEnabled())
        {
            if ($this->checkSession())
                return true;
        }

        return false;
    }

    public function isDynamicBlocksEnabled()
    {
        if ($this->_dynamicBlocks === null)
            $this->_dynamicBlocks = (bool)$this->getDbConfig('amfpc/general/dynamic_blocks');

        return $this->_dynamicBlocks;
    }

    public function getBlockSessionKey($name)
    {
        $session = new Amasty_Fpc_Model_Session();

        if ($session->isBlockUpdated($name))
            $result = isset($_COOKIE[$this->_sessionName]) ? $_COOKIE[$this->_sessionName] : '';
        else
        {
            $blockConfig = (array)Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);

            $persistent = false;
            if (isset($blockConfig['@attributes']))
            {
                $persistentAttributes = array_intersect_assoc(
                    $blockConfig['@attributes'],
                    array('persistent' => 1, 'customer' => 1, 'cart' => 1)
                );

                if (!empty($persistentAttributes))
                    $persistent = true;
            }

            if ($persistent && isset($_COOKIE['persistent_shopping_cart']))
                $result = $_COOKIE['persistent_shopping_cart'];
            else
                $result = self::getCustomerGroupId();
        }

        return $result;
    }

    public function getBlockCacheId($name)
    {
        $blockNode = Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);
        $scope = $blockNode ? (string) $blockNode['scope'] : '';

        $isUrlScope = ($scope == 'url');

        $session = $this->getBlockSessionKey($name);

        $url = $isUrlScope ? $_SERVER['REQUEST_URI'] : '';

        $mobile = self::isMobile() ? 'm' : false;
        $secure = self::getSecureKey();

        // TODO invalidate blocks cache on currency switch
        $currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : false;
        $store = self::getStoreCode();

        $key = $secure.$_SERVER['HTTP_HOST'].$url.$name.$session.$mobile.$currency.$store;

        return sha1($key);
    }

    public function getBlockCacheTag($name)
    {
        $session = $this->getBlockSessionKey($name);

        return 'amfpc_block_' . sha1($name.$session);
    }

    protected function fetch()
    {
        if ($data = $this->getCache()->load($this->getCacheKey()))
        {
            $boostRobots = $this->_boostRobots();

            if (!isset($_COOKIE[$this->_sessionName])
                && !$boostRobots
                && (bool)(string)Mage::app()->getConfig()->getNode('global/amfpc/miss_on_first_view')
            )
            {
                return false;
            }

            $sessionRequired = $this->_isSessionRequired() && !$boostRobots;

            $data = unserialize($data);
            $page = $data['content'];

            if (!$sessionRequired)
            {
                $page = str_replace('AMFPC_FORM_KEY', $this->_formKey, $page);
            }
            else
            {
                Mage::register('amfpc_new_session', true, true);
            }

            if ($this->isDynamicBlocksEnabled())
            {
                $requiredBlocks = $this->getRequiredBlocks($page);
                $ajaxBlocks = $this->getRequiredAjaxBlocks($page);

                $referer = self::getFullUrl();
                $referer = strtr(base64_encode($referer), '+/=', '-_,');

                $validBlocks = array();
                $invalidBlocks = array();
                foreach ($requiredBlocks as $name)
                {
                    $cookieBlock = Amasty_Fpc_Model_Config::getCookieNoticeBlockName();
                    if ($name == $cookieBlock && self::$_cookieUpdated)
                    {
                        $content = false;
                    }
                    else
                    {
                        $id = $this->getBlockCacheId($name);
                        $content = $this->getCache()->load($id);
                    }

                    if ($content !== false)
                    {
                        $content = preg_replace('#/referer/[A-Za-z0-9\-_,]+/#', "/referer/$referer/", $content);

                        if (!$sessionRequired)
                            $content = str_replace('AMFPC_FORM_KEY', $this->_formKey, $content);

                        $validBlocks[$name] = $content;
                    }
                    else
                        $invalidBlocks[] = $name;
                }

                foreach ($validBlocks as $name => $content)
                {
                    $blockNode = Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);

                    $debugName = $name;
                    if (isset($blockNode['parent']))
                        $debugName .= "[{$blockNode['parent']}]";

                    $this->debug($content, $debugName);
                    $page = str_replace("<amfpc name=\"$name\" />", $content, $page);
                }

                if (!empty($invalidBlocks))
                {
                    $cmsBlocks = (bool)(string)Mage::app()->getConfig()->getNode('global/amfpc/cms_blocks');

                    if ($cmsBlocks)
                    {
                        Mage::register('amfpc_cms_blocks', true, true);
                    }
                    else
                    {
                        $info = array(
                            'page' => $page,
                            'blocks' => array_merge($ajaxBlocks, $invalidBlocks),
                        );

                        Mage::register('amfpc_blocks', $info, true);
                    }
                    return false;
                }
                else
                {
                    if (!empty($ajaxBlocks))
                        $page = $this->addAjaxLoad($page, $ajaxBlocks);
                }
            }

            $page = preg_replace(
                '#<amfpc_ajax name="([^"]+)" />#s',
                '<div id="amfpc-\1"></div>',
                $page
            );

            if ($sessionRequired)
            {
                Mage::register('amfpc_page', $page, true);
                return false;
            }

            if (isset($_GET['___store']))
                setcookie('store', self::getStoreCode(), 0, '/', '.' . $_SERVER['HTTP_HOST']);

            $this->debug($page, 'Early page load');
            $this->addLoadTimeInfo($page);

            $this->_processPageActions($data);

            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');

            return $page;
        }
        else
        {
            Mage::register('amfpc_preserve', true, true);
            return false;
        }
    }

    protected function _processPageActions($data)
    {
        if (isset($data['category_id']) && !isset($data['product_id']) && isset($_SESSION['catalog']))
        {
            $_SESSION['catalog']['last_visited_category_id'] = $data['category_id'];
            $_SESSION['catalog']['last_viewed_category_id'] = $data['category_id'];
        }
        if (isset($data['product_id']) && isset($_SESSION['catalog']) && isset($data['store_id']))
        {
            $_SESSION['catalog']['last_viewed_product_id'] = $data['product_id'];

            $info = array(
                'product_id' => $data['product_id'],
                'store_id' => $data['store_id'],
                'customer_id' => isset($_SESSION['customer']['customer_id']) ? $_SESSION['customer']['customer_id'] : null,
                'visitor_id' => isset($_SESSION['core']['visitor_data']['visitor_id']) ? $_SESSION['core']['visitor_data']['visitor_id'] : null,
                'added_at' => date('Y-m-d H:i:s')
            );


            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            $connection->insertOnDuplicate($resource->getTableName('reports/viewed_product_index'), $info, array_keys($info));
        }
    }

    public static function getDbConfig($path)
    {
        if (self::$_configCache === null)
        {
            $resource = Mage::getSingleton('core/resource');
            $adapter = $resource->getConnection('core_read');

            $select = $adapter->select()
                ->from($resource->getTableName('core/config_data'), array('path', 'value'))
                ->where('path LIKE \'amfpc/%\' OR path IN (?)', self::$_additionalConfigs)
                ->where('scope_id=?', 0)
            ;

            self::$_configCache = $adapter->fetchAll($select, array(), PDO::FETCH_KEY_PAIR);
        }

        if (isset(self::$_configCache[$path]))
            return self::$_configCache[$path];
        else
            return null;
    }

    public function allowedDebugInfo()
    {
        if ($this->_debugInfo === null)
        {
            if (!isset($_SERVER['REMOTE_ADDR']))
            {
                $this->_debugInfo = false;
            }
            else
            {
                if ($this->_cookieDebugTrigger && isset($_COOKIE['amfpc_debug'])) {
                    $this->_debugInfo = true;
                    return true;
                }

                $ips = $this->getDbConfig('amfpc/debug/ip');
                $ips = preg_split('/[,\s]+/', $ips, -1, PREG_SPLIT_NO_EMPTY);

                $this->_debugInfo = empty($ips) || in_array($_SERVER['REMOTE_ADDR'], $ips);
            }
        }

        return $this->_debugInfo;
    }

    public function isDebugEnabled()
    {
        if ($this->_debug === null)
            $this->_debug = $this->getDbConfig('amfpc/debug/hints') && $this->allowedDebugInfo();

        return $this->_debug;
    }

    public function addLoadTimeInfo(&$html, $type = self::PAGE_LOAD_HIT)
    {
        global $amfpc_start_time;

        $displayPopup = $this->allowedDebugInfo() && $this->getDbConfig('amfpc/debug/load_time');
        $displayHidden = $this->getDbConfig('amfpc/debug/hidden_stats');

        if (!$displayHidden && !$displayPopup)
            return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET')
            return;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return;

        if (isset($_SERVER['REQUEST_TIME_FLOAT']))
        {
            $time = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3);
        }
        else if ($amfpc_start_time)
        {
            $time = round(microtime(true) - $amfpc_start_time + $this->_coreInitTime, 3);
        }
        else
            return;

        switch ($type)
        {
            case self::PAGE_LOAD_MISS:
                $typeTitle = "Cache Miss";
                break;
            case self::PAGE_LOAD_NEVER_CACHE:
                $typeTitle = "Never cache";
                break;
            case self::PAGE_LOAD_IGNORE:
                $typeTitle = "In ignore list";
                break;
            case self::PAGE_LOAD_HIT_UPDATE:
                $typeTitle = "Cache Hit<br/>(with block updates)";
                break;
            case self::PAGE_LOAD_HIT_SESSION:
                $typeTitle = "Cache Hit<br/>(with session initialization)";
                break;
            case self::PAGE_LOAD_IGNORE_PARAM:
                $typeTitle = "Contains ignored params";
                break;
            case self::PAGE_LOAD_CMS_UPDATE:
                $typeTitle = "Block updates";
                break;
            default:
                $typeTitle = "Cache Hit";
                break;
        }

        $popup = <<<POPUP
<div class="amfpc-info">
    <h1>Full Page Cache</h1>
    <div class="content">
        <div>$typeTitle</div>
        <strong>Load Time: </strong>{$time}s
    </div>
</div>
POPUP;

        $hiddenHtml = '<!-- AMFPC|' . strip_tags($typeTitle) . '|' . $time . 's -->';

        $resultHtml = '</body>';

        if ($displayHidden)
            $resultHtml = $hiddenHtml . $resultHtml;

        if ($displayPopup)
            $resultHtml = $popup . $resultHtml;

        $html = str_replace('</body>', $resultHtml, $html);
    }

    public function debug(&$html, $message)
    {
        if (!$this->isDebugEnabled())
            return false;

        $html = <<<HTML
<div class="amfpc-block-info updated">
<div class="amfpc-block-handle"
     onmouseover="$(this).parentNode.addClassName('active')"
     onmouseout="$(this).parentNode.removeClassName('active')"
>FPC: {$message}</div>$html</div>
HTML;
    }

    protected function addAjaxLoad($page, $names)
    {
        $names = implode(',', $names);
        $js = <<<AJAX
<script type="text/javascript">
new Ajax.Request('{$_SERVER['REQUEST_URI']}', {
    parameters: {amfpc_ajax_blocks: '$names'},
    onSuccess: function(response) {
        var blocks = response.responseText.evalJSON();
        for (var name in blocks)
        {
            $$('div[id=amfpc-'+name+']').each(function(element){
                element.replace(blocks[name]);
            });
        }
    }
});
</script>
AJAX;

        $page = str_replace("</head>", "\n$js\n</head>", $page);

        return $page;
    }

    protected function getRequiredAjaxBlocks($page)
    {
        $fpcConfig = new Amasty_Fpc_Model_Config();
        $config = $fpcConfig->getConfig();

        $ajaxBlocks = $config['ajax_blocks'];
        if (!$this->hasMessages())
        {
            if (isset($ajaxBlocks['global_messages']))
                unset($ajaxBlocks['global_messages']);

            if (isset($ajaxBlocks['messages']))
                unset($ajaxBlocks['messages']);
        }

        $ajaxBlocks = $this->getPageBlocks($page, array_keys($ajaxBlocks));

        return $ajaxBlocks;
    }

    protected function getRequiredBlocks($page)
    {
        $fpcConfig = new Amasty_Fpc_Model_Config();
        $config = $fpcConfig->getConfig();

        $blocks = $this->getPageBlocks($page, array_keys($config['blocks']));

        return $blocks;
    }

    protected function getPageBlocks($page, $names)
    {
        $blocks = array();

        if (preg_match_all('|<amfpc(_ajax)? name="(?P<name>[^"]+)"\s*/>|', $page, $matches))
        {
            $blocks = array_intersect($names, $matches['name']);
        }

        return $blocks;
    }

    protected function _isSessionRequired()
    {
        $version = Mage::getVersionInfo();

        if ($version['minor'] >= 8 && !isset($_COOKIE[$this->_sessionName]))
            return true;
        else
            return false;
    }

    protected function _boostRobots()
    {
        if ($this->isCrawlerRequest())
            return true;

        if ($this->getDbConfig('amfpc/robots/boost_robots'))
        {
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                return false;

            $agents = $this->getDbConfig('amfpc/robots/agents');
            if (!$agents)
                return false;

            return @preg_match('@' . $agents . '@', $_SERVER['HTTP_USER_AGENT']);
        }
        else
            return false;

    }

    public static function isMobile()
    {
        if (self::$_isMobile === null)
        {
            self::$_isMobile = false;
			if (strpos($_SERVER['HTTP_USER_AGENT'],'iPad')) {
				self::$_isMobile =false;
			}
			else
            {
				if (isset($_SERVER['HTTP_USER_AGENT']) && self::getDbConfig('amfpc/mobile/enabled'))
				{
					$regexp = self::getDbConfig('amfpc/mobile/agents');

					if (@preg_match('@' . $regexp . '@', $_SERVER['HTTP_USER_AGENT']))
					{
						self::$_isMobile = true;
					}
				}
			}
        }

        return self::$_isMobile;
    }

    public function inIgnoreList()
    {
        if ($ignore = self::getDbConfig('amfpc/pages/ignore_list'))
        {
            $ignoreList = preg_split('|[\r\n]+|', $ignore, -1, PREG_SPLIT_NO_EMPTY);
            $path = $_SERVER['REQUEST_URI'];

            foreach ($ignoreList as $pattern)
            {
                if (preg_match("|$pattern|", $path))
                    return true;
            }
        }

        return false;
    }

    protected function _checkCookiesAgreement()
    {
        if (isset($_COOKIE['user_allowed_save_cookie']))
        {
            $new = $_COOKIE['user_allowed_save_cookie'];
            $old = isset($_COOKIE['user_allowed_save_cookie_old']) ? $_COOKIE['user_allowed_save_cookie_old'] : false;

            if ($new !== $old)
            {
                setcookie('user_allowed_save_cookie_old', $new, -1, '/');
                self::$_cookieUpdated = true;
            }
        }
    }

    public function incrementHits()
    {
        if ($this->isCrawlerRequest())
            return;

        if (!self::getDbConfig('amfpc/stats/visits'))
            return;

        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_write');

        $adapter->insertOnDuplicate(
            $resource->getTableName('amfpc/url'),
            array(
                'url' => $this->getFullUrl(),
                'rate' => 1
            ),
            array('rate'=>new Zend_Db_Expr('rate+1'))
        );
    }

    public function isCrawlerRequest()
    {
        return isset($_COOKIE['amfpc_crawler']);
    }

    public function extractContent()
    {
        if (!Mage::app()->useCache('amfpc'))
            return false;

        global $amfpc_start_time;
        $amfpc_start_time = microtime(true);

        $this->_checkCookiesAgreement();

        if (!$this->ignore())
        {
            $this->incrementHits();

            $content = $this->fetch();

            if ($content)
            {
                Mage::app()->getResponse()->appendBody($content)->sendResponse();
                exit;
            }
        }

        $this->closeSession();

        return false;
    }
}
