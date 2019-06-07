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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method string getHeadline()
 * @method Justselling_Configurator_Model_Template setHeadline(string $value)
 * @method string getMassFactor()
 * @method Justselling_Configurator_Model_Template setMassFactor(string $value)
 * @method int getOptionValuePrice()
 * @method Justselling_Configurator_Model_Template setOptionValuePrice(int $value)
 * @method string getSvgExport()
 * @method Justselling_Configurator_Model_Template setSvgExport(string $value)
 * @method string getCombinedAdaptFactor()
 * @method Justselling_Configurator_Model_Template setCombinedAdaptFactor(string $value)
 * @method string getTemplateImage()
 * @method Justselling_Configurator_Model_Template setTemplateImage(string $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Template setId(int $value)
 * @method string getTitle()
 * @method Justselling_Configurator_Model_Template setTitle(string $value)
 * @method int getAltCheckout()
 * @method Justselling_Configurator_Model_Template setAltCheckout(int $value)
 * @method string getBaseImage()
 * @method Justselling_Configurator_Model_Template setBaseImage(string $value)
 * @method int getGroupLayout()
 * @method Justselling_Configurator_Model_Template setGroupLayout(int $value)
 * @method int getJpegQuality()
 * @method Justselling_Configurator_Model_Template setJpegQuality(int $value)
 * @method string getFontAdaptFactor()
 * @method Justselling_Configurator_Model_Template setFontAdaptFactor(string $value)
 * @method string getDescription()
 * @method Justselling_Configurator_Model_Template setDescription(string $value)
 * @method int getCombinedProductImage()
 * @method Justselling_Configurator_Model_Template setCombinedProductImage(int $value)
 * @method int getGroupEnumerate()
 * @method Justselling_Configurator_Model_Template setGroupEnumerate(int $value)
 * @method string getCombinedAdaptSize()
 * @method Justselling_Configurator_Model_Template setCombinedAdaptSize(string $value)
 * @method string getDesign()
 * @method Justselling_Configurator_Model_Template setDesign(string $value)
 * @method int getOptionValuePriceZero()
 * @method Justselling_Configurator_Model_Template setOptionValuePriceZero(int $value)
 */
class Justselling_Configurator_Model_Template extends Mage_Core_Model_Abstract
{
    protected $_nodesGraph;
    protected $_lastErrorMessage;

	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/template');
	}
	
	/**
	 * 
	 * get key value pairs for select boxes
	 * @return array
	 */
	public function toOptionArray()
    {        
        /* @var $collection Justselling_Configurator_Model_Mysql4_Template_Collection */
        $collection = $this->getCollection();
        $options = array();
        foreach( $collection->getItems() as $item) {
        	$options[] = array('value' => $item->id, 'label' => $item->title);
        }
        return $options;
    }
    
    /**
     * 
     * get linked template
     * @param integer $productOptionId
     */
    public function getLinkedTemplateId($productOptionId,$storeId=0)
    {
    	return $this->getResource()->getLinkedTemplateId($productOptionId,$storeId);
    }
    
    
   /**
	 * 
	 * link a template with a custom product option
	 * @param integer $productOptionId
	 * @param integer $templateId
	 * @return boolean
	 */
    public function linkTemplateWithProductOption($productOptionId,$templateId,$storeId=0)
    {
    	return $this->getResource()->linkTemplateWithProductOption($productOptionId,$templateId,$storeId);
    }
    
	public function unlinkTemplateWithProductOption($productOptionId,$templateId)
    {
    	return $this->getResource()->linkTemplateWithProductOption($productOptionId,$templateId);
    }
    
	public function removeTemplateFromProductOption($productOptionId,$templateId,$storeId=0)
    {
    	return $this->getResource()->removeTemplateFromProductOption($productOptionId,$templateId,$storeId);
    }
    
    public function getLinkedProducts($templateId,$storeId=0) {
    	return $this->getResource()->getLinkedProducts($templateId, $storeId);
    }
       
    public function clearProductCache($templateId = 0) {
    	if ($templateId) {
			$productsOptions = $this->getLinkedProducts($templateId, Mage::app()->getStore()->getCode());
			foreach ($productsOptions as $productOptionId) {
				$productoption = Mage::getModel("catalog/product_option")->load($productOptionId);
				$this->recursive_remove_directory(Mage::getBaseDir('media')."/configurator/cache/".$productoption->getProductId());
			}
		} else {
			$this->recursive_remove_directory(Mage::getBaseDir('media')."/configurator/cache/");
		}
	}
	
	protected function recursive_remove_directory($directory, $empty=FALSE) {
		Mage::Log("Delete folder: ".$directory);
    	if (substr($directory,-1) == '/') {
        	$directory = substr($directory,0,-1);
     	}
     	if (!file_exists($directory) || !is_dir($directory)) {
        	return FALSE;
     	} elseif (is_readable($directory)) {
        	$handle = opendir($directory);
         	while (FALSE !== ($item = readdir($handle))) {
            	if($item != '.' && $item != '..') {
               		$path = $directory.'/'.$item;
                 	if(is_dir($path)) {
                    	$this->recursive_remove_directory($path);
                 	} else {
                 		unlink($path);
              		}
         		}
      		}
     		closedir($handle);
       		if($empty == FALSE) {
      			if(!rmdir($directory)) {
        			return FALSE;
        		}
      		}
 		}
 		return TRUE;
 	}

 	/*
 	 * Returns an array (key/value) of all options and there default value
 	 */
 	public function getDefaultValues($templateId) {
 		$defaults = array();
 		$options = Mage::getModel("configurator/option")->getCollection()->addFieldToFilter("template_id", $templateId);
 		foreach ($options as $option) {
 			if ($option->getDefaultValue()) {
 				$defaults[$option->getId()] = $option->getDefaultValue();
 			}
 		}
 		return $defaults;
 	}

    /**
     * @param int $templateId
     * @param array $selectedtemplateOptions
     * @return mixed
     *
     * Sets the default values for all options which are not yet selected in the given array
     */
    public function setDefaultValues($templateId, $selectedtemplateOptions) {
 		$defaults = $this->getDefaultValues($templateId);
 		foreach ($defaults as $option_id => $value) {
 			if (!isset($selectedtemplateOptions[$option_id]))
 				$selectedtemplateOptions[$option_id] = $value;
 		}
 		return $selectedtemplateOptions;
 	}
 	
 	/**
 	/* Handle the active option tree of a template 
 	 */
 	 
 	/**
 	 * The function will read all options without a defined parent-option (parent_id is null)
 	 *
 	 * @param int $templateId
 	 * @return Justselling_Configurator_Model_Mysql4_Option_Collection
 	 */
 	protected function _getRoots($templateId)
 	{
 		if ($templateId) {
 			$key = 'PRODCONF_TEMPLATEROOTS_'.$templateId;
 			$items = Mage::helper("configurator")->readFromCache($key);
 			if ($items) {
 				return (array) $items;
 			}
 			/* @var $collection Varien_Data_Collection_Db */
 			$collection = Mage::getModel("configurator/option")->getCollection()
 				->addFieldToFilter('template_id', $templateId)
 				->addFieldToFilter('parent_id', array('null'=>true))
 				->setOrder('sort_order','ASC');
 			$items = $collection->getItems();
            Mage::helper("configurator")->writeToCache(
                $items,
                $key,
                array("PRODCONF","PRODCONF_TEMPLATE_".$templateId)
            );
 			return $items;
 		}
 		return NULL;
 	} 	
 	
 	/**
 	 * Adds all children from nodes collection for the given root option
 	 * to the children var
 	 */
    protected function _addChild($root,$nodes) {
 		foreach($nodes as $node) {
 			if( $root->getId() == $node->getParentId() ) {
 				array_push($root->children, $node);
 				$this->_addChild($node,$nodes);
 			}
 		}
 		return $root;
 	}

    /**
     * @param int $templateId
     * @return Justselling_Configurator_Model_Mysql4_Option_Collection
     */
    public function getTree($templateId)
 	{
 		$gtStart = microtime(true);

        /* Get all root options */
        $cache_key = "PRODCONF_ROOTS_".$templateId;
        if (Mage::helper("configurator")->readFromCache($cache_key)) {
            $roots = Mage::helper("configurator")->readFromCache($cache_key);
        } else {
            $roots = $this->_getRoots($templateId);
            Mage::helper("configurator")->writeToCache(
                $roots,
                $cache_key,
                array("PRODCONF","PRODCONF_TEMPLATE_".$templateId)
            );
        }
 	
 		/* Get all options that have a parent */
        $nodes = Mage::getModel("configurator/option")
            ->getCollection()
            ->addFieldToFilter("template_id",$templateId)
            ->addFieldToFilter('parent_id', array('notnull'=>true))
            ->setOrder('sort_order','ASC');
 	
 		/* Write all Values of the option to the values var */
        $gtStart1 = microtime(true);
 		if ($nodes) {
 			foreach($nodes as $node) {
                $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
                $cache_key = "PRODCONF_TEMPLATEOPTIONVALUES_".$templateId."_".$node->getId()."_".$currency;
                if (Mage::helper("configurator")->readFromCache($cache_key)) {
                    $node->values = Mage::helper("configurator")->readFromCache($cache_key);
                } else {
                    $node->values = $node->getValueCollection()->getItems();
                    Mage::helper("configurator")->writeToCache(
                        $node->values,
                        $cache_key,
                        array("PRODCONF","PRODCONF_TEMPLATE_".$templateId,"PRODCONF_OPTION_".$node->getId())
                    );
                }
 			}
 		}
        //Js_Log::log('time getTree::getNodeValues:' . (microtime(true) - $gtStart1), "profile", Zend_Log::DEBUG, true);

 		/* Write all Values of the root-options to the values var */
        $gtStart2 = microtime(true);
 		if ($roots) {
 			foreach($roots as $root) {
                $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
                $cache_key = "PRODCONF_TEMPLATEOPTIONVALUES_".$templateId."_".$root->getId()."_".$currency;
                if (Mage::helper("configurator")->readFromCache($cache_key)) {
                    $root->values = Mage::helper("configurator")->readFromCache($cache_key);
                } else {
 				    $root->values = $root->getValueCollection()->getItems();
                    Mage::helper("configurator")->writeToCache(
						$root->values,
                        $cache_key,
                        array("PRODCONF","PRODCONF_TEMPLATE_".$templateId,"PRODCONF_OPTION_".$root->getId())
                    );
                }
                $this->_addChild($root, $nodes);
 			}
 		}
        //Js_Log::log('time getTree::getRootValues:' . (microtime(true) - $gtStart2), "profile", Zend_Log::DEBUG, true);

        //Js_Log::log('time getTree ' . (microtime(true) - $gtStart), "profile", Zend_Log::DEBUG, true);
 		return $roots;
 	}

    /**
     * @param array $nodes
     * @param array $selectedTemplateOptions
     * @param int $selected
     * @return array
     */
    public function renderTree($nodes, &$selectedTemplateOptions, $selected = 1, $optionsArray, $stockItemArray = array())
 	{
 		$_start = microtime(true);
 		$options = array();

        $_start2 = microtime(true);
 		if ($nodes)
 			foreach($nodes as $node_key => $node) { // Array of options
 			$active_node = true;
 			if ($active_node) {
 				/* Check for specific values otherwise */
                $_start3 = microtime(true);
                $cache_key = "PRODCONF_VALUECHILDSTATUS_".$node->getId();
                if (Mage::helper("configurator")->readFromCache($cache_key) || is_array(Mage::helper("configurator")->readFromCache($cache_key))) {
                    $valueChildStati = Mage::helper("configurator")->readFromCache($cache_key);
                } else {
                    $valueChildStati = Mage::getModel ( "configurator/valuechildstatus" )->getCollection ();
                    $valueChildStati->addFieldToFilter ( "option_id", $node->getId() );
                    Mage::helper("configurator")->writeToCache(
                        $valueChildStati,
                        $cache_key,
                        array("PRODCONF","PRODCONF_TEMPLATE_".$node->getTemplateId,"PRODCONF_OPTION_".$node->getId())
                    );
                }
                //Js_Log::log('time valuechildstatus: ' . (microtime(true) - $_start3), "profile", Zend_Log::DEBUG, true);

                $_start3 = microtime(true);
 				if ($valueChildStati && $selectedTemplateOptions) {
 					foreach ( $valueChildStati as $valueChildStatus ) {
 						foreach ( $selectedTemplateOptions as $optionValueId ) {
 							if ($valueChildStatus->getOptionValueId () == $optionValueId) {
 								$node->setPrice ( $node->getPrice () + $valueChildStatus->getPrice () );
 							}
 						}
 					}
 				}
                //Js_Log::log('time value status: ' . (microtime(true) - $_start3), "profile", Zend_Log::DEBUG, true);
 	
 				// Check, if there is a linked magento product which is not in stock
                if (Mage::getStoreConfig('productconfigurator/stock/active')) {
                    $_start4 = microtime(true);
                    $option = $optionsArray[$node->getId()];
                    if ($option && $option->getProductId()) {
                        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */

                        if($stockItemArray[$option->getProductId()]){
                            $qtyStock = $stockItemArray[$option->getProductId()];
                        }else{
                            $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($option->getProductId());
                            $stockItemArray[$option->getProductId()] = $qtyStock;
                        }
                        if (!$qtyStock->getIsInStock()){
                            $active_node = false;
                        }
                    }
                    //Js_Log::log('time check stock: ' . (microtime(true) - $_start4), "profile", Zend_Log::DEBUG, true);
                }

 				// Add node to the tree when it is active
 				if ($active_node)
 					$options [] = $node;
 				$options = array_merge ( $options, $this->renderTree ( $node->children, $selectedTemplateOptions, $selected, $optionsArray, $stockItemArray));
 			}
 		}
        //Js_Log::log('time nodes: ' . (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);

        //Js_Log::log('time renderTree: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
 		return $options;
 	}

    /**
     * @param int $template_id
     * @return array
     */
    public function getAllOptionIds($template_id = NULL) {
        if (!$template_id) {
            $template_id = $this->getId();
        }
        $option_ids =   array();
        $options = Mage::getModel("configurator/option")->getCollection();
        $options->addFieldToFilter("template_id", $template_id);
        foreach ($options as $option) {
            $option_ids[] = $option->getId();
        }
        return $option_ids;
    }

    /**
     * Returns all Options.
     * @param null $templateId optional, may be null
     * @return Justselling_Configurator_Model_Mysql4_Option_Collection
     */
    public function getAllOptions($templateId=null) {
        if (!$templateId) {
            $templateId = $this->getId();
        }
        $options = Mage::getModel("configurator/option")->getCollection();
        $options->addFieldToFilter("template_id", $templateId);
        return $options;
    }

    /**
     * @param int $template_id
     * @return array
     */
    public function getAllGroups($template_id = NULL) {
        if (!$template_id) {
            $template_id = $this->getId();
        }
        $groups = array();
        $options = Mage::getModel("configurator/option")->getCollection();
        $options->addFieldToFilter("template_id", $template_id);
        foreach ($options as $option) {
            if ($option->getOptionGroupId()) {
                $groups[$option->getOptionGroupId()] = Mage::getModel("configurator/optiongroup")->load($option->getOptionGroupId())->getSortOrder();
            }
        }

        asort($groups);

        foreach ($groups as $index => $sort_order) {
            $groups[$index] = Mage::getModel("configurator/optiongroup")->load($index)->getTitle();
        }

        return $groups;
    }

    /**
     * @param array $tree
     * @param int $option_id
     * @return bool
     */
    public function checkIfOptionIsInTree($tree, $option_id) {
 		foreach ($tree as $option) {
 			if ($option->getId() == $option_id)
 				return true;
 			if ($option->children)
 				$this->checkIfOptionIsInTree($option->children, $option_id);
 		}
 		return false;
 	}

    protected function _setError($error) {
        $this->_lastErrorMessage = $error;
    }

    public function getLastErrorMessage() {
        $errorMessage = $this->_lastErrorMessage;
        $this->_setError(null);
        return $errorMessage;
    }

    /************************************************************/
    /* Methods to check for loops in the configuration template */

    protected function _getIndexOfNode($id, $nodes) {
        $i = 0;
        foreach ($nodes as $node) {
            if ($id == $node->getId()) {
                return $i;
            }
            $i++;
        }
        return false;
    }

    protected function _getNeightbors($element, $graph, $size) {
        $neightbors = array();
        for ($index=0; $index<$size; $index++) {
            if ($graph[$element][$index]) {
                $neightbors[] = $index;
            }
        }
        return $neightbors;
    }

    protected function _getOptionInformationByIndex($option_index) {
        $nodes = Mage::getModel("configurator/option")->getCollection();
        $nodes->addFieldToFilter("template_id", $this->getId());

        $index = 0;
        foreach ($nodes as $node) {
            if ($index == $option_index) {
                return ($node->getTitle()." (".$node->getId().")");
            }
            $index++;
        }
    }

    protected function _dfs($graph, $size) {
        $marked = array();
        $predecessor = array();
        $edge_set = array();
        for ($index=0; $index<$size; $index++) {
            $marked[$index] = 0;
            $predecessor[$index] = null;
        }
        for ($index=0; $index<$size; $index++) {
            if ($marked[$index] == 0) {
                $this->_dfs_visit($graph, $size, $index, $marked, $predecessor, $edge_set);
            }
        }
        if (count($edge_set) > 0) {
            $error = Mage::helper('configurator')->__("Loop in configuration detected").": ";
            foreach($edge_set as $edge) {
                $error .= " ".$edge;
            }
            $this->_setError($error);
            return true;
        } else {
            return false;
        }
    }

    protected function _dfs_visit($graph, $size, $index, &$marked, &$predecessor, &$edge_set) {
        $marked[$index] = 1;
        foreach ($this->_getNeightbors($index, $graph, $size) as $neightbor) {
            if ($marked[$neightbor] == 0) {
                $predecessor[$neightbor] = $index;
                $this->_dfs_visit($graph, $size, $neightbor, $marked, $predecessor, $edge_set);
            } elseif ($marked[$neightbor] == 1) {
                $edge_set[] = $this->_getOptionInformationByIndex($index)." > ".$this->_getOptionInformationByIndex($neightbor);
            }
        }
        $marked[$index] = 2;
    }

    public function checkForLoops($templateId) {
        if (!$this->_nodesGraph) {
            $nodes = Mage::getModel("configurator/option")->getCollection();
            $nodes->addFieldToFilter("template_id", $templateId);

            $i = 0;
            $size = count($nodes);
            if ($size > 0) {
                $this->_nodesGraph = array();
                $this->_nodesGraph = array_fill(0, $size, false);
                foreach ($nodes as $node) {
                    $toIndex = $this->_getIndexOfNode($node->getParentId(), $nodes);
                    if ($toIndex) {
                        if (!is_array($this->_nodesGraph[$i])) {
                            $this->_nodesGraph[$i] = array();
                            $this->_nodesGraph[$i] = array_fill(0, $size, 0);
                        }
                        $this->_nodesGraph[$i][$toIndex] = 1;
                    }
                    $i++;
                }
            }
        }

        if ($this->_dfs($this->_nodesGraph, $size)) {
            return true;
        }
        return false;
    }


	/**
	 * Checks the currently set image path reference and copies it to the related (new structure) target location. In
	 * case the location has been adjusted it is set in the image reference (data) and - maybe - persisted.<br/>
	 * This method may be called in case of import, or copy of an OptionGroup.
	 * @param $persist bool true if the adjusted image reference should be saved, false (default) if not
	 * @return bool true if at least one has been adjusted, false if not
	 */
	public function adjustImageReferences($persist=false) {
		$hasBeenAdjusted = false;
		$imageFields = array('base_image', 'template_image');
		foreach ($imageFields as $imgField) {
			if ($adjusted = Justselling_Configurator_Model_Export_Processor::adjustAndCopyImageLocation($this, $imgField)) {
				$hasBeenAdjusted = true;
			}
		}
		if ($hasBeenAdjusted && $persist) {
			$this->save();
		}
		return $hasBeenAdjusted;
	}



	/**
	 * Returns the image path (without image file name), relative from 'media' folder.
	 * @param bool $absolute
	 * @param string $field the field to calculate the location path from (i.e. 'option_image')
	 * @return bool|string
	 */
	public function calculateImagePath($absolute=false, $field='') {
		if (!$this->getId()) {
			Js_Log::log("Call of Template::calculateImagePath on not persisted item!", $this, Zend_Log::ERR);
			return false;
		}
		$prefix = $absolute ? Mage::getBaseDir('media').DS : '';
		return $prefix.'configurator'.DS.$this->getId();
	}
}