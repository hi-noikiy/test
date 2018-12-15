<?php
class EM_Changemodeview_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
	const XML_PATH_ENABLED          = 'changemodeview/general/enable';
	const XML_PATH_CHOOSECATEGORY		= 'changemodeview/general/choose_category';
	const XML_PATH_ENABLEMOBILE		= 'changemodeview/general/enable_mobile';
	public function checkenableview()
	{
		if(Mage::getStoreConfigFlag(self::XML_PATH_ENABLED))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function checkenablemobile()
	{
		if(Mage::getStoreConfigFlag(self::XML_PATH_ENABLEMOBILE))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	public function getCategorie123()
	{
		$check1=$this->checkenableview();
		if($check1==1)
		{
			$strCategories=Mage::getStoreConfig(self::XML_PATH_CHOOSECATEGORY);
			$arrCategories = explode(",", $strCategories);
			return $arrCategories;
		}
		else
			return $arrCategories=null;
	}
	public function isMobileviewlist()  
	{  
		$check1=$this->checkenableview();
		$check2=$this->checkenablemobile();
		if($check1&&$check2)
		{
			$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|"  
						 . "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|"  
						 . "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|"  
						 . "symbian|smartphone|mmp|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|"  
						 . "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220"  
						 . ")/i";  

			if (preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']))) {  
				return TRUE;  
			}  

			if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {  
				return TRUE;  
			}      

			$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));  
			$mobile_agents = array(  
				'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
				'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
				'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
				'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
				'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
				'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
				'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
				'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
				'wapr','webc','winw','winw','xda ','xda-');  

			if (in_array($mobile_ua,$mobile_agents)) {  
				return TRUE;  
			}  

			if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {  
				return TRUE;  
			}
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$pos_blackberry = strrpos($ua, "blackberry");
			$pos_webkit = strrpos($ua, "webkit");
			if (!($pos_blackberry === false))
			{
			  if (!($pos_webkit === false))
			  {
				return TRUE;
			  }
			}
			if (preg_match('/BB10/i',$ua))
			{
				return TRUE;
			}	
			return FALSE;  
		}
		else
		{
			return FALSE;  
		}
	}  	
	 protected $_collection = null;

    /**
     * GET parameter page variable
     *
     * @var string
     */
    protected $_pageVarName     = 'p';

    /**
     * GET parameter order variable
     *
     * @var string
     */
    protected $_orderVarName        = 'order';

    /**
     * GET parameter direction variable
     *
     * @var string
     */
    protected $_directionVarName    = 'dir';

    /**
     * GET parameter mode variable
     *
     * @var string
     */
    protected $_modeVarName         = 'mode';

    /**
     * GET parameter limit variable
     *
     * @var string
     */
    protected $_limitVarName        = 'limit';

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder      = array();

    /**
     * List of available view types
     *
     * @var string
     */
    protected $_availableMode       = array();

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    protected $_enableViewSwitcher  = true;

    /**
     * Is Expanded
     *
     * @var bool
     */
    protected $_isExpanded          = true;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $_orderField          = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction           = 'asc';

    /**
     * Default View mode
     *
     * @var string
     */
    protected $_viewMode            = null;

    /**
     * Available page limits for different list modes
     *
     * @var array
     */
    protected $_availableLimit  = array();

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit  = array(10=>10,20=>20,50=>50);

    /**
     * @var bool $_paramsMemorizeAllowed
     */
    protected $_paramsMemorizeAllowed = true;

    /**
     * Retrieve Catalog Config object
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * Init Toolbar
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_orderField  = Mage::getStoreConfig(
            Mage_Catalog_Model_Config::XML_PATH_LIST_DEFAULT_SORT_BY
        );

        $this->_availableOrder = $this->_getConfig()->getAttributeUsedForSortByArray();

        switch (Mage::getStoreConfig('catalog/frontend/list_mode')) {
            case 'grid':
                $this->_availableMode = array('grid' => $this->__('Grid'));
                break;

            case 'list':
                $this->_availableMode = array('list' => $this->__('List'));
                break;

            case 'grid-list':
                $this->_availableMode = array('grid' => $this->__('Grid'), 'list' =>  $this->__('List'));
                break;

            case 'list-grid':
                $this->_availableMode = array('list' => $this->__('List'), 'grid' => $this->__('Grid'));
                break;
        }
        $this->setTemplate('catalog/product/list/toolbar.phtml');
    }

    /**
     * Disable list state params memorizing
     */
    public function disableParamsMemorizing()
    {
        $this->_paramsMemorizeAllowed = false;
        return $this;
    }

    /**
     * Memorize parameter value for session
     *
     * @param string $param parameter name
     * @param mixed $value parameter value
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    protected function _memorizeParam($param, $value)
    {
        $session = Mage::getSingleton('catalog/session');
        if ($this->_paramsMemorizeAllowed && !$session->getParamsMemorizeDisabled()) {
            $session->setData($param, $value);
        }
        return $this;
    }

    /**
     * Set collection to pager
     *
     * @param Varien_Data_Collection $collection
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
	    public function setCollection($collection)
    {
	   $this->_collection = $collection;
	    $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
		
		$this->_collection->getSize();
      /*	if($this->getCurrentOrder()=='mostviewd') {
		$this->_collection->getSelect()->
                 joinLeft('ln_mostviewed AS _table_views',
                         ' _table_views.entity_id = e.entity_id',
                         '_table_views.view_count AS views')->
                 order('views DESC'); 
			$sql = $this->_collection->getSelectSql(true);
           $this->_collection->getSelect()->reset()->from(
                   array('e' =>new Zend_Db_Expr("({$sql})")),
                   array('e' => "*")
               );  
		} */
            if($this->getCurrentOrder()=='mostviewd') {    
                $storeId = (int) Mage::app()->getStore()->getId();
                 $date = new Zend_Date(); 
                $toDate = $date->getDate()->get('Y-MM-dd');
                 $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd'); 
                $this->_collection->getSelect() 
               ->joinLeft('ln_mostviewed AS _table_views',
                         ' _table_views.entity_id = e.entity_id',
                         '_table_views.view_count AS views')         
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_daily')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )->
                group('e.entity_id')->
            order(array('sold_quantity DESC', 'views DESC'));
                $sql = $this->_collection->getSelectSql(true);
           $this->_collection->getSelect()->reset()->from(
                   array('e' =>new Zend_Db_Expr("({$sql})")),
                   array('e' => "*")
               );  
            } 
           // echo  $this->_collection->getSelect(); exit;
      $this->_collection->load();
		parent::setCollection($this->_collection);
        return  $this;
    }

    /**
     * Return products collection instance
     *
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Getter for $_pageVarName
     *
     * @return string
     */
    public function getPageVarName()
    {
        return $this->_pageVarName;
    }

    /**
     * Retrieve order field GET var name
     *
     * @return string
     */
    public function getOrderVarName()
    {
        return $this->_orderVarName;
    }

    /**
     * Retrieve sort direction GET var name
     *
     * @return string
     */
    public function getDirectionVarName()
    {
        return $this->_directionVarName;
    }

    /**
     * Retrieve view mode GET var name
     *
     * @return string
     */
    public function getModeVarName()
    {
        return $this->_modeVarName;
    }

    /**
     * Getter for $_limitVarName
     *
     * @return string
     */
    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam($this->getPageVarName())) {
            return $page;
        }
        return 1;
    }

    /**
     * Get grit products sort order field
     *
     * @return string
     */
     public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->_orderField;

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->getRequest()->getParam($this->getOrderVarName());
		/*pradeep to select deafult order or sort  start */
		if($order =='') {
			$order=$this->_orderField;
		}
		/*pradeep to select deafult order or sort  end */
        if ($order && isset($orders[$order])) {
            if ($order == $defaultOrder) {
                Mage::getSingleton('catalog/session')->unsSortOrder();
            } else {
                $this->_memorizeParam('sort_order', $order);
            }
        } else {
            $order = Mage::getSingleton('catalog/session')->getSortOrder();
        }
        // validate session value
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }
        $this->setData('_current_grid_order', $order);
        return $order;
    }


    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = array('asc', 'desc');
        $dir = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));
		/*pradeep to select deafult direction  start */
		if($dir =='') {
			$dir=$this->_direction;
			
		}
		/*pradeep to select deafult direction  start */
		
        if ($dir && in_array($dir, $directions)) {
            if ($dir == $this->_direction) {
                Mage::getSingleton('catalog/session')->unsSortDirection();
            } else {
                $this->_memorizeParam('sort_direction', $dir);
            }
        } else {
            $dir = Mage::getSingleton('catalog/session')->getSortDirection();
        }
        // validate direction
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }
        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setDefaultOrder($field)
    {
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }
        return $this;
    }

    /**
     * Set default sort direction
     *
     * @param string $dir
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setDefaultDirection($dir)
    {
        if (in_array(strtolower($dir), array('asc', 'desc'))) {
            $this->_direction = strtolower($dir);
        }
        return $this;
    }

    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
	public function getAvailableOrders()
    {
		$this->addOrderToAvailableOrders('mostviewd', 'Most Popular');
		//$this->addOrderToAvailableOrders('bestseller', 'Best Seller');
		$this->setDefaultOrder('mostviewd');
		$this->setDefaultDirection('asc');
		return $this->_availableOrder;
    }

    /**
     * Set Available order fields list
     *
     * @param array $orders
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setAvailableOrders($orders)
    {
        $this->_availableOrder = $orders;
        return $this;
    }

    /**
     * Add order to available orders
     *
     * @param string $order
     * @param string $value
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function addOrderToAvailableOrders($order, $value)
    {
        $this->_availableOrder[$order] = $value;
        return $this;
    }
    /**
     * Remove order from available orders if exists
     *
     * @param string $order
     * @param Mage_Catalog_Block_Product_List_Toolbar
     */
    public function removeOrderFromAvailableOrders($order)
    {
        if (isset($this->_availableOrder[$order])) {
            unset($this->_availableOrder[$order]);
        }
        return $this;
    }

    /**
     * Compare defined order field vith current order field
     *
     * @param string $order
     * @return bool
     */
    public function isOrderCurrent($order)
    {
        return ($order == $this->getCurrentOrder());
    }

    /**
     * Retrieve Pager URL
     *
     * @param string $order
     * @param string $direction
     * @return string
     */
    public function getOrderUrl($order, $direction)
    {
        if (is_null($order)) {
            $order = $this->getCurrentOrder() ? $this->getCurrentOrder() : $this->_availableOrder[0];
        }
        return $this->getPagerUrl(array(
            $this->getOrderVarName()=>$order,
            $this->getDirectionVarName()=>$direction,
            $this->getPageVarName() => null
        ));
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Retrieve current View mode
     *
     * @return string
     */
    public function getCurrentMode()
    {
        $mode = $this->_getData('_current_grid_mode');
        if ($mode) {
            return $mode;
        }
        $modes = array_keys($this->_availableMode);
	
		$category=Mage::registry('current_category');
		if($category)
		{
			$path = $category->getPath();
			$ids = explode('/', $path);
			if (isset($ids[2])){
				$topParent = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($ids[2]);
				$categoryId_new=$topParent->getId();
			}
			$checkcat=$this->getCategorie123();
		}
		else
		{
			$checkcat=false;	
		}	
		$checkmobileview=$this->isMobileviewlist();
		if(($checkcat&&in_array($categoryId_new,$checkcat))||$checkmobileview )
		{
			$defaultMode ="list";
			$mode = $this->getRequest()->getParam($this->getModeVarName());
			if ($mode) {
				if ($mode == $defaultMode) {
					Mage::getSingleton('catalog/session')->unsDisplayMode();
				} else {
					$this->_memorizeParam('display_mode', $mode);
				}
			} else {
				$mode = $defaultMode;
			}
			if (!$mode || !isset($this->_availableMode[$mode])) {
				$mode = $defaultMode;
			}
			$this->setData('_current_grid_mode', $mode);
			return $mode;
		}
		else
		{
				$mode = $this->_getData('_current_grid_mode');
			if ($mode) {
				return $mode;
			}
			$modes = array_keys($this->_availableMode);
			$defaultMode ="grid";
			$mode = $this->getRequest()->getParam($this->getModeVarName());
			if ($mode) {
				if ($mode == $defaultMode) {
					Mage::getSingleton('catalog/session')->unsDisplayMode();
				} else {
					$this->_memorizeParam('display_mode', $mode);
				}
			} else {
				$mode = $defaultMode;
			}

			if (!$mode || !isset($this->_availableMode[$mode])) {
				$mode = $defaultMode;
			}
			$this->setData('_current_grid_mode', $mode);
			return $mode;	
		}
		
		
		
			
    }

    /**
     * Compare defined view mode with current active mode
     *
     * @param string $mode
     * @return bool
     */
    public function isModeActive($mode)
    {
        return $this->getCurrentMode() == $mode;
    }

    /**
     * Retrieve availables view modes
     *
     * @return array
     */
    public function getModes()
    {
        return $this->_availableMode;
    }

    /**
     * Set available view modes list
     *
     * @param array $modes
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setModes($modes)
    {
        if(!isset($this->_availableMode)){
            $this->_availableMode = $modes;
        }
        return $this;
    }

    /**
     * Retrive URL for view mode
     *
     * @param string $mode
     * @return string
     */
    public function getModeUrl($mode)
    {
        return $this->getPagerUrl( array($this->getModeVarName()=>$mode, $this->getPageVarName() => null) );
    }

    /**
     * Disable view switcher
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function disableViewSwitcher()
    {
        $this->_enableViewSwitcher = false;
        return $this;
    }

    /**
     * Enable view switcher
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function enableViewSwitcher()
    {
        $this->_enableViewSwitcher = true;
        return $this;
    }

    /**
     * Is a enabled view switcher
     *
     * @return bool
     */
    public function isEnabledViewSwitcher()
    {
        return $this->_enableViewSwitcher;
    }

    /**
     * Disable Expanded
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function disableExpanded()
    {
        $this->_isExpanded = false;
        return $this;
    }

    /**
     * Enable Expanded
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function enableExpanded()
    {
        $this->_isExpanded = true;
        return $this;
    }

    /**
     * Check is Expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return $this->_isExpanded;
    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        if ($this->getCurrentMode() == 'list') {
            if ($default = $this->getDefaultListPerPage()) {
                return $default;
            }
            return Mage::getStoreConfig('catalog/frontend/list_per_page');
        }
        elseif ($this->getCurrentMode() == 'grid') {
            if ($default = $this->getDefaultGridPerPage()) {
                return $default;
            }
            return Mage::getStoreConfig('catalog/frontend/grid_per_page');
        }
        return 0;
    }

    /**
     * Add new limit to pager for mode
     *
     * @param string $mode
     * @param string $value
     * @param string $label
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function addPagerLimit($mode, $value, $label='')
    {
        if (!isset($this->_availableLimit[$mode])) {
            $this->_availableLimit[$mode] = array();
        }
        $this->_availableLimit[$mode][$value] = empty($label) ? $value : $label;
        return $this;
    }

    /**
     * Retrieve available limits for current view mode
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        $currentMode = $this->getCurrentMode();
        if (in_array($currentMode, array('list', 'grid'))) {
            return $this->_getAvailableLimit($currentMode);
        } else {
            return $this->_defaultAvailableLimit;
        }
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @return array
     */
    protected function _getAvailableLimit($mode)
    {
        if (isset($this->_availableLimit[$mode])) {
            return $this->_availableLimit[$mode];
        }
        $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
        $perPageValues = (string)Mage::getStoreConfig($perPageConfigKey);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
            return ($perPageValues + array('all'=>$this->__('All')));
        } else {
            return $perPageValues;
        }
    }

    /**
     * Get specified products limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->getRequest()->getParam($this->getLimitVarName());
        if ($limit && isset($limits[$limit])) {
            if ($limit == $defaultLimit) {
                Mage::getSingleton('catalog/session')->unsLimitPage();
            } else {
                $this->_memorizeParam('limit_page', $limit);
            }
        } else {
            $limit = Mage::getSingleton('catalog/session')->getLimitPage();
        }
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    /**
     * Retrieve Limit Pager URL
     *
     * @param int $limit
     * @return string
     */
    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl(array(
            $this->getLimitVarName() => $limit,
            $this->getPageVarName() => null
        ));
    }

    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+1;
    }

    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+$collection->count();
    }

    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('product_list_toolbar_pager');

        if ($pagerBlock instanceof Varien_Object) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setLimitVarName($this->getLimitVarName())
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimit())
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'))
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }

        return '';
    }
}
			