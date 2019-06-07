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
 
class Justselling_Configurator_Block_Renderer extends Mage_Core_Block_Template
{
	
	const GROUP_VERTICAL_LAYOUT   			= 1;
	const GROUP_HORIZONTAL_LAYOUT   		= 2;
	const GROUP_HORIZONTAL_WIZARD_LAYOUT   	= 3;
	
	const DESIGN_MORE_INFO_TOOLTIP			= "tooltip";
	const DESIGN_MORE_INFO_FADEIN			= "fade_in";
	
	const LISTIMAGE_STYLE_IMAGE_ONLY	    = 0;
    const LISTIMAGE_STYLE_IMAGE_LABEL       = 1;
	
	/**
	 * 
	 * currently selected template options
	 * @var array
	 */
	public $selectedTemplateOptions = null;
	public $templateOptions = null;
	public $jsTemplateOption = null;

    public function getOptionsHtml() {
        $templateId = $this->getTemplateId();
        $group_id = $this->getGroupId();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $cache_key = "PRODCONF_OPTIONSHTML_".$templateId."_".$group_id."_".$currency;

        $defaults = $this->getDefaultValues();
        $enable_caching = false;
        $selected_template_options = $this->getSelectedTemplateOptions();
        if (serialize($defaults) == serialize($selected_template_options)) {
            $enable_caching = true;
        }

        if ($enable_caching && Mage::helper("configurator")->readFromCache($cache_key, false)) {
            $html = Mage::helper("configurator")->readFromCache($cache_key, false);
        } else {
            $html =  $this->getChildHtml('options',false);
            Mage::helper("configurator")->writeToCache(
                $html,
                $cache_key,
                array("PRODCONF", "PRODCONF_TEMPLATE_".$templateId, "BLOCK_HTML"),
                false
            );
        }
        return $html;
    }

    /**
     *
     * set selected template options
     * @param array $templateOptions
     */
    public function buildSelectedTemplateOptions()
    {
        $templateOptions = array();
        $_product = $this->getProduct();
        if (!isset($_product) || !$_product->getConfigureMode()) { // Using Default Values from configuration template
            // Check Default Values
            $defaults = $this->getDefaultValues();
            if ($defaults) {
                foreach ($defaults as $key => $value) {
                    if (!isset($templateOptions[$key]))
                        $templateOptions[$key] = $value;
                }
            }

            // Check for deeplink information in url
            $templateOptions = $this->validateDeepLink($templateOptions);

        } else { // Get preconfigures values when coming back from the cart
            $preconfigure_values = $_product->getPreconfiguredValues()->getOptions();

            // Get preconfigured option for the current option-id
            $preconfigure_values = $preconfigure_values[$this->getProductOption()->getId()];

            reset($preconfigure_values); $key = key($preconfigure_values);
            $preconfigure_values = $preconfigure_values[$key]['template'];

            $defaults = $this->getDefaultValues($this->getTemplateOptions(0));
            foreach($preconfigure_values as $key => $value) {
                $defaults[$key] =  $value;
            }
            $templateOptions = $defaults;
        }

        return $templateOptions;
    }

    /**
     * @param null $template_id
     * @return object
     */
    protected function getAllTemplateOptions($template_id = NULL) {
        if (!$template_id) {
            $template_id = $this->getTemplateId();
        }
        $options = Mage::getModel("configurator/option")->getCollection();
        $options->addFieldToFilter("template_id", $template_id);
        $options->load();

        return $options;
    }

    /**
     * @param int $template_id
     * @return array
     */
    protected function buildStockFilter($template_id) {
        $stockStatus = array();

        /* get list of linked products, if they are in stock and store it to $stockStatus */
        if (Mage::getStoreConfig('productconfigurator/stock/active') && ! Mage::registry ( "stock_status" )) {
            /* Read all options for the current template */
            $options = Mage::getModel ( "configurator/option" )->getCollection ();
            $options->addFieldToFilter ( "template_id", $template_id );
            $optionIds = $options->getAllIds ();
            $valueIds = array ();
            /* Read all values of the options */
            foreach ( $optionIds as $optionId ) {
                $values = Mage::getModel ( "configurator/value" )->getCollection ();
                $values->addFieldToFilter ( "option_id", array (
                    "in" => $optionIds
                ) );
                $values->addFieldToFilter ( "product_id", array (
                    'neq' => 'NULL'
                ) );
                foreach ( $values->getAllIds () as $id )
                    $valueIds [] = $id;
            }
            /* read all options with linked products */
            $options = Mage::getModel ( "configurator/option" )->getCollection ();
            $options->addFieldToFilter ( "template_id", $template_id );
            $options->addFieldToFilter ( "product_id", array (
                'neq' => 'NULL'
            ) );
            $optionIds = $options->getAllIds ();
            $stockStatus = array (
                "options" => array (),
                "values" => array ()
            );
            /* go to all the options and values and check the stock status */
            foreach ( $optionIds as $optionId ) {
                $option = Mage::getModel ( "configurator/option" )->load ( $optionId );
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $qtyStock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $option->getProductId () );
                $stockStatus ["options"] [$optionId] = $qtyStock->getIsInStock ();
            }
            foreach ( $valueIds as $valueId ) {
                $value = Mage::getModel ( "configurator/value" )->load ( $valueId );
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $qtyStock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $value->getProductId () );
                $stockStatus ["values"] [$valueId] = $qtyStock->getIsInStock ();
            }
        }

        return $stockStatus;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function setSelectedTemplateOptions($options) {
        if (!is_null(Mage::registry('selected_template_options'))) {
            Mage::unregister('selected_template_options');
        }
        Mage::register('selected_template_options', $options);
        return $options;
    }

    /**
     * @return array|mixed|null
     */
    public function getSelectedTemplateOptions() {
        if (Mage::registry("selected_template_options")) {
            return Mage::registry("selected_template_options");
        }
        return null;
    }

    /**
     * @param $id
     * @return bool|array
     */
    public function getSelectedTemplateOption($id) {
        $options = $this->getSelectedTemplateOptions();
        if (isset( $options[$id]))
            return $options[$id];
        else
            return false;
    }

    /**
     * @param array $templateOptions
     * @return array|null
     *
     * Read deep link information from params
     */
    public function validateDeepLink($templateOptions) {
        $deeplink_active = false;

        // Check for deeplink params like o_x_y where x is the
        // product_option_id and y is the template_option_id
        foreach ( $this->getRequest ()->getParams () as $key => $value ) {
            $parts = explode ( "_", $key );
            if (sizeof ( $parts ) == 3) {
                $option_id = $parts [1];
                $template_option_id = $parts [2];

                if ($option_id == $this->getProductOption ()->getId ()) {
                    $templateOptions [$template_option_id] = $value;
                    $deeplink_active = true;
                }
            }
        }

        // Checking for Deep Links without product_option_id like options[x]=y
        $options = $this->getRequest ()->getParam ( 'options', false );
        if ($options && sizeof ( $options ) > 0) {
            foreach ( $options as $key => $value ) {
                if (!is_array($value)) {
                    $templateOptions [$key] = $value;
                    $deeplink_active = true;
                }
            }
        }

        return $templateOptions;
    }

    public function hasDeepLink() {
        $hasDeeplLink = false;
        // Check for deeplink params like o_x_y where x is the
        // product_option_id and y is the template_option_id
        foreach ( $this->getRequest ()->getParams () as $key => $value ) {
            if(strpos($key,'o_') !== false){
                $parts = explode ( "o_", $key );
                if (sizeof ( $parts ) >= 1) {
                    $hasDeeplLink = true;
                    break;
                }
            }
        }

	   	return $hasDeeplLink;
    }

    /* TODO: Restructure */


    public function getChildCode($name = '', $useCache = true, $sorted = false) {
    	if ($name != "") {
    		$templatefile = $name.".phtml";
    		$child = $this->getLayout()->createBlock('configurator/renderer_'.$name);
    		$child->setTemplate('configurator/renderer/'.$templatefile);
    		
    		switch ($name) {
    			case "header":
    				$child->setHeadline($this->getHeadline());
    				$child->setTemplateImage($this->getTemplateImage());
    				$child->setGroupLayout($this->getGroupLayout());
    				$child->setTemplateGroupsHtml($this->getTemplateGroupsHtml());
    				break;
    			case "postpricerules":
    				$child->setJsTemplateOption($this->getJsTemplateOption());
                    $child->setProductOption($this->getProductOption());
    				$child->setPostpricerules($this->getPostpricerules());
    				break;
    			case "js":
    				$child->setJsTemplateOption($this->getJsTemplateOption());
    				$child->setGroupLayout($this->getGroupLayout());
    				$child->setRenderer($this);
    				break;
    		}
    		return $child->toHtml();
    	}
    	
    	return false;
    }

    protected function getDefaultValues($templateOptions = null) {
        if (is_null($templateOptions)) {
            $templateOptions = $this->getAllTemplateOptions();
        }
    	$defaults = NULL;
		foreach ( $templateOptions as $templateOption ) {
			if ($templateOption->getDefaultValue ()) {
				if ($templateOption->getPlaceholder () && in_array ( $templateOption->getType (), array (
						'text',
						'area'
				) )) {
					// do nothing
				} else {
					$defaults [$templateOption->getId ()] = $templateOption->getDefaultValue ();
				}
			}
		}
		return $defaults;
    }
    

    
    public function getProduct()
    {
    	if (!Mage::registry('product') && $this->getProductId()) {
    		$product = Mage::getModel('catalog/product')->load($this->getProductId());
    		Mage::register('product', $product);
    	}
        if (!Mage::registry('product') && Mage::registry('current_product')) {
            $product = Mage::registry('current_product');
            Mage::register('product', $product);
        }
    	return Mage::registry('product');
    }
    
	public function getHeadline() {
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		return $templateModel->getHeadline();
	}
	
	public function getTemplateImage() {
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		
		if (!$templateModel->getTemplateImage())
			return NULL;
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$templateModel->getTemplateImage();
	}	

	public function getTemplateDesign($id) {
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());
			
		$templateModel->load($templateId);
		
		$data = unserialize($templateModel->getDesign());
		if (isset($data[$id]))
			return $data[$id];
		else
			return false;
	}
	
	public function getActiveCombinedProductImage() {
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		
		$status = $templateModel->getCombinedProductImage();

		return $status;
	}
	
	public function getGroupLayout() {
		
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		
		$status = $templateModel->getGroupLayout();

		return $status;
	}

	public function getGroupEnumerate() {
		
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		
		$status = $templateModel->getGroupEnumerate();

		return $status;
	}	

	public function getGroupTabsize() {
		
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
			
		$templateModel->load($templateId);
		
		$status = $templateModel->getGroupTabsize();

		return $status;
	}
		
	public function getGroupCount() {
		if (!$this->groupcount) {
			$groups = array();
			foreach($this->getTemplateOptions() as $templateOption) {
				if ($templateOption->getOptionGroupId() !== null)
					$groups[$templateOption->getOptionGroupId()]	= 1;		
			}
			$this->groupcount = sizeof($groups);
		}
		
		return $this->groupcount;
	}

    public function getAllGroups() {
        return Mage::getModel("configurator/template")->getAllGroups($this->getTemplateId());
    }

	public function getGroupImage($groupId){
		if($groupId){
			$group = Mage::getModel("configurator/optiongroup")->load($groupId);
			if($group->getGroupImage()){
				return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .$group->getGroupImage();
			}
		}
		return false;
	}

	/**
	 * 
	 * set product option
	 * @param Mage_Catalog_Model_Product_Option $productOption
	 * @return Justselling_Configurator_Block_Renderer
	 */
	public function setProductOption($productOption) {
        if (Mage::registry('product_option_id')) {
            Mage::unregister('product_option_id');
        }
        Mage::register('product_option_id', $productOption);

		return $this;
	}
	
	/**
	 * 
	 * get product option
	 * @return Mage_Catalog_Model_Product_Option
	 */
	public function getProductOption()
	{
        if (Mage::registry('product_option_id')) {
            return Mage::registry('product_option_id');
        }
		return null;
	}
	
	public function getTemplateId() {
		$templateModel = Mage::getModel('configurator/template');
		
		if( ($storeId=$this->getProductOption()->getProduct()->getStoreId()) != 0 )
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId(),$storeId);			
		else
			$templateId = $templateModel->getLinkedTemplateId( $this->getProductOption()->getId());	
		
		return $templateId;
	}
	
	public function getTemplateGroupsHtml() {
		$html = "<ul>";
		$groups = Mage::getModel("configurator/optiongroup")->getCollection();
		$groups->addFilter('template_id',$this->getTemplateId());
		$groups->setOrder('sort_order', 'ASC');
		$count = 0;
		foreach ($groups as $group) {
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->addFilter('template_id',$this->getTemplateId());
			$options->addFilter('option_group_id',$group->getId());
			$count++;
			if (count($options)) {
				
				$html .= "<li ";
				if ($count == count($groups)) { $html .= "class=\"last\" "; }
				$html .= "><a href=\"#group-".$group->getId()."\">".$group->getTitle()."</a></li>";
			} 
		}
		$html .= "</ul>";
		
		return $html;
		
	}

	public function hasWebservice(){
		$templateId = $this->getTemplateId();
		$options = Mage::getModel( "configurator/option" )->getCollection();
		$options->addFieldToFilter( "template_id", $templateId );
		$options->addFieldToFilter( "type", 'http' );
		if($options->count() > 0){
			return 'true';
		}else{
			return 'false';
		}
	}
	
	public function getTemplateOptions($selected = 1) {
        $_start = microtime(true);

		if ($this->templateOptions)  {
            // Mage::log("getTemplateOptions".(microtime(true)-$_start));
            return $this->templateOptions;
        }

		$templateModel = Mage::getModel('configurator/template');
		$templateId = $this->getTemplateId();

        // Load or build the template option tree
        $cache_key = "PRODCONF_TEMPLATETREE_".$templateId;
        if (Mage::helper("configurator")->readFromCache($cache_key)) {
            $tree = Mage::helper("configurator")->readFromCache($cache_key);
        } else {
		    $tree = $templateModel->getTree($templateId);
            Mage::helper("configurator")->writeToCache(
                $tree,
                $cache_key,
                array("PRODCONF","PRODCONF_TEMPLATE_".$templateId)
            );
        }

		/* get list of linked products, if they are in stock and store it to $stockStatus */
		if (Mage::getStoreConfig('productconfigurator/stock/active') && ! Mage::registry ( "stock_status" )) {
			/* Read all options for the current template */
			$options = Mage::getModel ( "configurator/option" )->getCollection ();
			$options->addFieldToFilter ( "template_id", $templateId );
			$optionIds = $options->getAllIds ();
			$valueIds = array ();
			/* Read all values of the options */
			foreach ( $optionIds as $optionId ) {
				$values = Mage::getModel ( "configurator/value" )->getCollection ();
				$values->addFieldToFilter ( "option_id", array (
						"in" => $optionIds 
				) );
				$values->addFieldToFilter ( "product_id", array (
						'neq' => 'NULL' 
				) );
				foreach ( $values->getAllIds () as $id )
					$valueIds [] = $id;
			}
			/* read all options with linked products */
			$options = Mage::getModel ( "configurator/option" )->getCollection ();
			$options->addFieldToFilter ( "template_id", $templateId );
			$options->addFieldToFilter ( "product_id", array (
					'neq' => 'NULL' 
			) );
			$optionIds = $options->getAllIds ();
			$stockStatus = array (
					"options" => array (),
					"values" => array () 
			);
			/* go to all the options and values and check the stock status */
			foreach ( $optionIds as $optionId ) {
				$option = Mage::getModel ( "configurator/option" )->load ( $optionId );
				/* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
				$qtyStock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $option->getProductId () );
				$stockStatus ["options"] [$optionId] = $qtyStock->getIsInStock ();
			}
			foreach ( $valueIds as $valueId ) {
				$value = Mage::getModel ( "configurator/value" )->load ( $valueId );
				/* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
				$qtyStock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $value->getProductId () );
				$stockStatus ["values"] [$valueId] = $qtyStock->getIsInStock ();
			}
			/* save the result to registry */
			Mage::register ( "stock_status", $stockStatus );
		}
		$stockStatus = Mage::registry("stock_status");

        $templateModel->setId($templateId);
        $selectedTemplateOptions = $this->getSelectedTemplateOptions();


        $optionsCollection = $this->getAllTemplateOptions($templateId);
        $optionsArray = array();
        foreach($optionsCollection as $option){
            $optionsArray[$option->getId()] = $option;
        }

        $options = $templateModel->renderTree($tree, $selectedTemplateOptions, $selected, $optionsArray);
		
		$groups = Mage::getModel("configurator/optiongroup")->getCollection();
		$groups->addFilter('template_id',$templateId);
		foreach ($options as $option) {
			if ($option->getOptionGroupId()) {
				foreach ($groups as $group) {
					if ($group->getId() == $option->getOptionGroupId()) {
						$option->setGroupSortOrder($group->getSortOrder());
						$option->setGroupTitle($group->getTitle());
						$option->setGroupId($group->getId());
						$option->setGroupImage($group->getImage());
					}
				}
			} else {
				$option->setGroupSortOrder(0);
			}
		}
		
		usort($options,array($this,"compare"));
		
		$this->templateOptions = $options;
        // Mage::log("getTemplateOptions ".(microtime(true)-$_start));
		return $options;
	}
	
	protected static function compare($option1,$option2) {
		
		$group_pos1 = (int) $option1->getGroupSortOrder();
		$group_pos2 = (int) $option2->getGroupSortOrder();
		if ($group_pos1 == $group_pos2) {
			$pos1 = (int) $option1->getSortOrder();
			$pos2 = (int) $option2->getSortOrder();		
			if( $pos1 == $pos2 ) return 0;		
			return ( $pos1 < $pos2 ) ? -1 : 1;
		}
		return ( $group_pos1 < $group_pos2 ) ? -1 : 1;	
	}
	
	public function getSelectHtml(Justselling_Configurator_Model_Option $templateOption)
	{
		$valueItems = $templateOption->values;

		$options = array(""=>"-- Please Select --");
		if( count($valueItems) > 0 )
		{
			foreach($valueItems as $valueItem) {
				$price = (float) $templateOption->getPrice() + (float) $valueItem->getPrice();
				$price = ' +'. number_format($price,2,",",""). ' ' . Mage::app()->getStore()->getCurrentCurrency()->getCode();
				$options[$valueItem->getId()] = $valueItem->getTitle() . $price;
			}			
		}
		
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'class' => 'select template-option required-option-select'
            ))
            ->setName('options['.$this->getProductOption()->getId().'][template]['.$templateOption->getId().']')
            ->setOptions($options);

        $values = $this->getSelectedTemplateOptions();
        if( isset($values[$templateOption->getId()]) ) {
        	$select->setValue($values[$templateOption->getId()]);
        }

        return $select->getHtml();
	}
	
	public function getPrice(Justselling_Configurator_Model_Option $templateOption) {
		$values = $this->getSelectedTemplateOptions();
		$value = isset($values[$templateOption->getId()]) ? $values[$templateOption->getId()] : false;
		$price = $templateOption->getCalculatedPrice($value,$values);
		
		if( in_array($templateOption->getType(),array('select','listimage','radiobuttons','selectcombi','listimagecombi'))  ) {			
			$valueIds = array();
			$test = "";
			foreach($templateOption->values as $objValue) {
				$test.= $objValue->getId()." ";	
				$valueIds[] = $objValue->getId();		
			}				
			if( isset($values[$templateOption->getId()]) && !in_array($values[$templateOption->getId()], $valueIds) ) {
				$price = 0;
			}			
		}
		
		if($value && $price)
            return $price;
		
		return 0;
	}

    /**
     * @return string
     */
    public function getJsTemplateOption() {
        if (Mage::registry("js_template_id")) {
            return Mage::registry("js_template_id");
        }
		return $this->jsTemplateOption;
	}

    /**
     * @param string $instance
     * @return $this
     */
    public function setJsTemplateOption($instance) {
		$this->jsTemplateOption = $instance;

        if (Mage::registry("js_template_id")) {
            Mage::unregister("js_template_id");
        }
        Mage::register("js_template_id", $instance);
		return $this;
	}
	
	public function getOptionSku($skuDelimiter='-') {
		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$sku = $customOptionModel->getOptionSku($this->getSelectedTemplateOptions(),$skuDelimiter);		
		return $sku;
	}
	
	public function getTemplateOptionHtml(Justselling_Configurator_Model_Option $templateOption)
	{
        if (is_object($templateOption)) {
            $_start2 = microtime(true);

            if ($templateOption->getFrontendType()) {
                $type = strtolower($templateOption->getFrontendType());
                $block_name = "configurator".$type."/".$type;
                $template = "configurator".$type."/type/".$type.".phtml";
            } else {
                $type = strtolower($templateOption->getType());
                $block_name = "configurator/".$type;
                $template = "configurator/type/".$type.".phtml";
            }

            $js_template_id = $this->getJsTemplateOption();
            $product_option = $this->getProductOption();
            $selected_template_options = $this->getSelectedTemplateOptions();
            $defaults = $this->getDefaultValues();
            //Js_Log::log('time prepare option '. (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);

            $enable_caching = false;
            if (serialize($defaults) == serialize($selected_template_options)) {
                //Js_Log::log('caching for block is active', "profile", Zend_Log::DEBUG, true);
                //$enable_caching = true; // Diasbale caching for single blocks. We will cache the whole configurator HTML
            }

            if (Mage::registry('option_id')) {
                Mage::unregister('option_id');
            }
            Mage::register('option_id', $templateOption->getId());

		    $block = $this->getLayout()->createBlock($block_name);
            if (!is_object($block)) {
                Mage::Log("ERROR: Can't create block of type ".$block_name);
                return "";
            } else {
                $block->setTemplate($template);
                $block->setTemplateOption($templateOption);
                $block->setProductOption($product_option);
                $block->setSelectedTemplateOptions($selected_template_options);
                $block->setJsTemplateOption($js_template_id);
                Mage::unregister('option_id');
                //Js_Log::log('time prepare part 2 option '. (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);

                if ($enable_caching ) {
                    $html = $block->toHtml();
                } else {
                    $html = $block->toHtmlIgnoreCache();
                }

                //Js_Log::log('time option is ready '. (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);
                return $html;
            }
        }
        return false;
	}
		
	public function getCombinedProductImage() {
		$templateId = Mage::getModel('configurator/template')->getLinkedTemplateId($this->getProductOption()->getOptionId());
		$template = Mage::getModel('configurator/template')->load($templateId);
		return Mage::helper('configurator/Combinedimage')->getCombinedProductImage($this->getProductOption()->getProduct(), $template, $this->getSelectedTemplateOptions(), $this->getJsTemplateOption());
	}
	
	public function getPostpricerules() {
		$rules = Mage::getModel("configurator/postpricerule")->getCollection();
		$rules->addFilter("template_id",$this->getTemplateId());
		
		$rules_js = array();
		$i = 1;
		foreach($rules as $rule) {
			$rules_js[] = $rule->getPostPriceRule();
			$i++;
		}
		
		return $rules_js;
	}

    public function maskadeInfoForAttribute($attribute) {
        return (str_replace('"',"'",$attribute));
    }

	public function getOptionsWithGroupId()
	{

		$collection = Mage::getModel('configurator/option')->getTemplateOptionsWithGroup($this->getTemplateId());

		$options = array();

		foreach( $collection->getItems() as $item) {
			if(isset($options[$item->getOptionGroupId()]) && !is_array($options[$item->getOptionGroupId()])){
				$options[$item->getOptionGroupId()] = array();
			}
			$options[$item->getOptionGroupId()][] = array( 'id' => $item->getId() ,'title' => $item->getTitle(), 'price' => 0);
		}

		$json =  Zend_Json_Encoder::encode($options);
		return $json;
	}

	public function getRules(){
		$collection = Mage::getModel('configurator/rules')->getCollection();
		$collection = $collection->addFilter('template_id', $this->getTemplateId());

		$rules = array();

		foreach( $collection->getItems() as $item) {
			$rules[] = array( 'scope' => $item->getScope() ,'appliedfor' => $item->getAppliedfor(), 'operatorvalue' => $item->getOperatorvalue(), 'value' => $item->getValue(), 'message' => $item->getMessage(), 'when_executed' => $item->getWhenExecuted(), 'option_id' =>$item->getOptionId());
		}

		$json =  Zend_Json_Encoder::encode($rules);
		return $json;
	}
}