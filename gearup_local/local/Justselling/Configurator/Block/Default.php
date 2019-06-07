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
 
class Justselling_Configurator_Block_Default extends Mage_Core_Block_Template
{	
	/**
	 * 
	 * template option
	 * @var Justselling_Configurator_Model_Option
	 */
	public $templateOption = null;
	
	/**
	 * 
	 * javasctipt template option instance
	 * @var string
	 */
	public $jsTemplateOption = null;
	
	/**
	 * 
	 * product option
	 * @var Mage_Catalog_Model_Product_Option
	 */
	public $productOption = null;
	
	/**
	 * 
	 * currently selected Options
	 * @var array
	 */
	public $selectedTemplateOptions = null;

    private static $_transportObject;
	
	/** @var array local cache for different key/value pairs */
	private $_localCache = array();

    protected function _construct()
    {
        parent::_construct();
        $option_id = Mage::registry('option_id');

        $this->addData(array(
            'cache_lifetime'    => 7200,
            'cache_tags'        => array(
                "PRODCONF",
                "PRODCONF_OPTION_".$option_id
            ),
            'cache_key'         => $this->getCacheKey()
        ));

        $cc = Mage::app()->getStore()->getCurrentCurrencyCode();
        $cachekey[] = Mage::app()->getLocale()->currency($cc)->getLocale();
        $cachekey[] = $cc;
        $cachekey[] = Mage::helper("configurator")->getTaxFactor();

        return $cachekey;
    }


    public function getCacheKeyInfo()
    {
        $cachekey = array(
            'CONFIGURATOR_BLOCK',
            Mage::app()->getStore()->getId()
        );

        $product_option = Mage::registry('product_option_id');
        if ($product_option) {
            $cachekey[] = $product_option->getOptionId();
        }

        $option_id = Mage::registry('option_id');
        if ($option_id) {
            $cachekey[] = $option_id;
        }

        return $cachekey;
    }
	
	public function getProduct()
	{
		if (!Mage::registry('product') && $this->getProductId()) {
			$product = Mage::getModel('catalog/product')->load($this->getProductId());
			Mage::register('product', $product);
		}
		return Mage::registry('product');
	}

	public function getBlacklist() {
		$blacklistValues = Mage::getModel('configurator/optionblacklist')
		 ->getCollection()
		 ->addFilter('option_id',$this->getTemplateOption()->getId());
		 if (isset($blacklistValues) && $blacklistValues->getSize() > 0)
		 	return true;
		else
		 return false;
	}
	
	/**
	 * 
	 * set product option
	 * @param Mage_Catalog_Model_Product_Option $productOption
	 * @return Justselling_Configurator_Block_Default
	 */
	public function setProductOption($productOption) 
	{
		$this->productOption = $productOption;	
		return $this;
	}
	
	/**
	 * 
	 * get product option
	 * @return Mage_Catalog_Model_Product_Option
	 */
	public function getProductOption()
	{
		return $this->productOption;
	}
	
	/**
	 * 
	 * set template option
	 * @param Justselling_Configurator_Model_Option $templateOption
	 * @return Justselling_Configurator_Block_Default
	 */
	public function setTemplateOption(Justselling_Configurator_Model_Option $templateOption) 
	{
		$this->templateOption = $templateOption;
		return $this;
	}
	
	/**
	 * 
	 * set js template option instance
	 * @param string $instance
	 */
	public function setJsTemplateOption($instance) {
		$this->jsTemplateOption = $instance;
	}
	
	/**
	 * 
	 * get js template option instance
	 * @param string $instance
	 */
	public function getJsTemplateOption() {
		return $this->jsTemplateOption;
	}
	
	/**
	 * 
	 * get template option
	 * @return Justselling_Configurator_Model_Option
	 */
	public function getTemplateOption()
	{
		return $this->templateOption;
	}

    /**
     * get selected value model
     * @return Justselling_Configurator_Model_Value
     */
    public function getSelectedValueModel()
    {
        if ($this->getSelectedValue()) {
            $value = Mage::getModel("configurator/value")->load($this->getSelectedValue());
            if ($value->getId()) {
                return $value;
            }
        }

        return null;
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
     * @return mixed|null
     */
    public function getSelectedTemplateOptions() {
        if (Mage::registry("selected_template_options")) {
            $options = Mage::registry("selected_template_options");
            return $options;
        }
        return null;
    }
	
	public function getOptionSku($skuDelimiter='-') {
		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$sku = $customOptionModel->getOptionSku($this->getSelectedTemplateOptions(),$skuDelimiter);
		return $sku;
	}

    public function isCombinedAdaptSizeField() {
        if (is_object($this->getTemplateOption()) && $this->getTemplateOption()->getTemplateId()) {
            $template = Mage::getModel("configurator/template")->load($this->getTemplateOption()->getTemplateId());
            if ($template->getCombinedAdaptSize() &&
                ($this->getTemplateOption()->getAltTitle() == "width" || $this->getTemplateOption()->getAltTitle() == "height"))
                return true;
        }
        return false;
    }

	/**
	 * 
	 * get product option id
	 * @return integer
	 */
	public function getProductOptionId()
	{
		return $this->getProductOption()->getId();
	}

    public function getTemplateModel() {
        $template = Mage::getModel("configurator/template")->load($this->getTemplateOption()->getTemplateId());
        return $template;
    }

	public function getOption() {
		return $this->templateOption;
	}
	
	public function getOptionId()
	{
		return $this->templateOption->getId();
	}
	
	public function getOptionAltTitle()
	{
		return $this->templateOption->getAltTitle();
	}
	
	public function getOptionTitle()
	{
		return $this->templateOption->getTitle();
	}

    public function getOptionInfo()
    {
        return $this->templateOption->getInfo();
    }

    public function getOptionMoreInfo()
    {
        return $this->templateOption->getMoreInfo();
    }

	public function getDefaultValue()
	{
		return $this->templateOption->getDefaultValue();
	}	
	
	public function getValue()
	{
		return $this->templateOption->getValue();
	}	

	public function getIsRequire()
	{
		return $this->templateOption->getIsRequire();
	}

    public function getSortOrder()
    {
        return $this->templateOption->getSortOrder();
    }

    public function getIsVisible()
    {
        return $this->templateOption->getIsVisible();
    }

	public function getPlaceholder()
	{
		return $this->templateOption->getPlaceholder();
	}
	
	public function getUploadType()
	{
		return $this->templateOption->getUploadType();
	}
	
	public function getUploadFiletypes()
	{
		return $this->templateOption->getUploadFiletypes();
	}
	
	public function getUploadMaxsize()
	{
		return $this->templateOption->getUploadMaxsize();
	}
	
	
	/**
	 * 
	 * get form element name
	 * @return string
	 */
	public function getName($expression = false)
	{
        $expressionName = "";
        if ($expression == true) {
            $expressionName= "expression-";
        }
		return "options[".$this->getProductOptionId()."][".$this->getJsTemplateOption()."][template][".$expressionName.$this->getOptionId()."]";
	}
	
	public function getFormOptionId() 
	{	
		return "options-".$this->getProductOptionId()."-".$this->getJsTemplateOption()."-template-".$this->getOptionId();
	}
		
	/**
	 * get form element id
	 * @return string
	 */
	public function getId($suffix=null) 
	{
		$name = $this->getName();
		$id = str_replace(array("[","]"), "-", $name);
		$id = str_replace("--", "-", $id);
		$id.=  ($suffix === null) ? "" : $suffix;
		
		if( substr($id,-1) == "-") {
			$id = substr($id,0,-1);
		}
		
		return $id;
	}
	
	public function getDynamicsId() {
		$id = $this->getName();		
		if( substr($id,-1) == "-") {
			$id = substr($id,0,-1);
		}
		$id = str_replace("options","dynamics",$id);
		$id = str_replace("[template]","",$id);
		
		return $id;
	}
	
	/**
	 * 
	 * get currently selected value
	 * @return string
	 */
	public function getSelectedValue() 
	{
        $options = $this->getSelectedTemplateOptions();
		if( empty($options[$this->templateOption->getId()]) )
			return '';
		else 
			return $options[$this->templateOption->getId()];
	}

    public function formatPrice($price,$inclCurrency=true)
    {
        $html = $price;

        if( $inclCurrency ) {
            $html = Mage::helper('core')->formatCurrency($html);
        }

        return $html;
    }
	
	public function getValidationClasses()
	{
		$class = "";
		
		if( $this->templateOption->getIsRequire() ) {
			switch($this->templateOption->getType()) {				
				case "select":
				case "selectcombi":
                case "listimage":
                case "listimagecombi":
					$class.= "validate-select";
					break;
				case "radiobuttons":
					$class.= "validate-one-required-by-name ";
					break;
				default:
					$class.= "required-entry ";
					break;
			}
			
		}
		
		if( $this->templateOption->min_value ) {
			$class.= "validate-min-".$this->templateOption->getMinValue()." ";
		}
		
		if( $this->templateOption->max_value ) {
			$class.= "validate-max-".$this->templateOption->getMaxValue()." ";
		}
		
		return $class;
	}
	
	public function getValidationMethods() {
		
		$script = "";
		return $script;
	}
	
	public function getCalculatedPrice($value) {
		if( !is_null($this->getSelectedTemplateOptions()) && !is_null($value) ) {
			$price = $this->templateOption->getCalculatedPrice($value,$this->getSelectedTemplateOptions());
			return $price;
		}
		return 0;
	}
	
	public function getCurrentPrice() {
        $options = $this->getSelectedTemplateOptions();
		if (isset ( $options [$this->templateOption->getId ()] ))
			$optionvalueId = $options [$this->templateOption->getId ()];
		else
			return 0;
		$optionvalue = Mage::getModel ( "configurator/value" )->load ( $optionvalueId );
		if ($this->templateOption->getType () == "selectcombi") {
			return $this->getCalculatedPrice ( $optionvalue->getId () );
		}
		if ($optionvalue) {
			return $optionvalue->getPrice ();
		}
		return 0;
	}

	public function getPrice($price, $qty=0){

		$templateErpOn = $this->getTemplateModel()->getAltCheckout();
		if($templateErpOn && is_numeric($qty) && $qty > 0){
			$price = $price * $qty;
		}
               return $price;     
	}

    public function getPriceAsFormatedString() {
        $price = $this->templateOption->getPrice() * Mage::helper("configurator")->getTaxFactor();
        $modus = $this->getShowOptionValuePrice();
        if (!($modus == "2") && ($price || $this->getShowOptionValuePriceZero())) {
            $formated_price = "<span>[";
            if ($price < 0) {
                $formated_price .= "-";
            } else {
                $formated_price .= "+";
            }
            $formated_price .= $this->formatPrice($price);
            $formated_price .= "]</span>";
            return $this->formatCurrency($formated_price);
        } else {
            return "";
        }
    }

	public function getPriceChange($price) {
		$pricechange = "";

		if($this->getOption() && $this->getOption()->getOperator() == 'expression' && $this->getOption()->getType() == 'selectcombi') {
			return "";
		}

		if ($this->getShowOptionValuePrice() == "2") return "";  	// Don't show price
		if ($this->getShowOptionValuePrice() == "0") { 				// 0 absolute, 1 relative
			if ($price == 0 && !$this->getShowOptionValuePriceZero()) return "";
			$prefix = "+";
			if ((double)$price < 0)
				$prefix = "";

			$formattedPrice = Mage::helper('core')->currency($price,true,false);
			$pricechange = "<span>[".$prefix .$formattedPrice."]</span>";
		} else {
			$relativePrice = $price - $this->getCurrentPrice();
			if ($relativePrice == 0 && !$this->getShowOptionValuePriceZero()) return "";
			$pricechange .= "<span>[";
			if ($relativePrice >= 0){
				$pricechange .="+";
			}
			$formattedPrice = Mage::helper('core')->currency($relativePrice,true,false);
			$pricechange .= $formattedPrice."]</span>";
		}

		return $this->formatCurrency($pricechange);
	}



    protected function formatCurrency($item) {
        $pricechange = str_replace("+","+ ",$item);
        $pricechange = str_replace("-","- ",$item);
        $pricechange = str_replace("EUR","€",$item);
        return $item;
    }
 	
	/**
	 * A (maybe cached) Justselling_Configurator_Model_Option instance for the given ID.
	 * @param $templateId The template option ID
	 * @return Justselling_Configurator_Model_Option
	 */
	protected function _getCachedTemplateOption($templateId=null) {
	    $id = is_null($templateId) ? $this->templateOption->getId() : $templateId;
	    $cacheKey = 'configuratorTemplateOption-'.$id;
	    if (!array_key_exists($cacheKey, $this->_localCache)) {
	        $option = Mage::getModel("configurator/option")->load($id);
	        $this->_localCache[$cacheKey] = $option;
	    }
	    return $this->_localCache[$cacheKey];
	}
	
	/**
	 * A (maybe cached) Justselling_Configurator_Model_Option instance for the given ID.
	 * @param int $id The template ID
	 * @return Justselling_Configurator_Model_Template
	 */
	protected function _getCachedTemplateForId($id) {
	    $cacheKey = 'configuratorTemplate-'.$id;
	    if (!array_key_exists($cacheKey, $this->_localCache)) {
	        $option = Mage::getModel("configurator/template")->load($id);
	        $this->_localCache[$cacheKey] = $option;
	    }
	    return $this->_localCache[$cacheKey];
	}
		
	
	public function getShowOptionValuePrice() {
	    $option = $this->_getCachedTemplateOption();
	    $templateId = $option->getTemplateId();
		$template = $this->_getCachedTemplateForId($templateId);
		return $template->getOptionValuePrice();
	}

	public function getShowOptionBlacklistingText() {
	    $option = $this->_getCachedTemplateOption();
	    $templateId = $option->getTemplateId();
		$template = $this->_getCachedTemplateForId($templateId);

        $design = unserialize($template->getDesign());
		if(isset($design['blacklist_text_display']) &&  $design['blacklist_text_display']){
			return "1";
		} else {
			return "0";
		}
	}

	public function getShowOptionValuePriceZero() {
		$option = $this->_getCachedTemplateOption();
		$templateId = $option->getTemplateId();
		$template = $this->_getCachedTemplateForId($templateId);
		return $template->getOptionValuePriceZero();
    }

	public function hasChildren() {
		$options = Mage::getModel("configurator/option")->getCollection();
		$options->addFieldToFilter("parent_id", $this->templateOption->getId());
		return ($options->getSize() > 0);
	}
	
	public function isLastMatrix() {
	    $_lmStart = microtime(true);
	    $isLastMatrix = false;
		$option = $this->_getCachedTemplateOption();  //Mage::getModel("configurator/option")->load($this->templateOption->getId());
		$last_id = $option->getId();
		while ($parent = $option->getParentId()) {
			$option = $this->_getCachedTemplateOption($parent);  //Mage::getModel("configurator/option")->load($parent);
			if ($option->getType() == "matrixvalue") {
				$last_id = $option->getId();
			}
		}
		if ($last_id == $this->templateOption->getId()) {
		    $isLastMatrix = true;
		}
		return $isLastMatrix;
	}
	
	public function getPricelist() {
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $cache_key = "PRODCONF_PRICELIST_OPTION_".$this->templateOption->getId()."_".$currency;
        if (Mage::helper("configurator")->readFromCache($cache_key)) {
            $priceListItems = Mage::helper("configurator")->readFromCache($cache_key);
        } else {
            $priceListItems = Mage::getModel("configurator/pricelist")->getCollection();
            $priceListItems->addFieldToFilter("option_id", $this->templateOption->getId());
            $priceListItems->addOrder("CAST(`value` AS SIGNED)","ASC");

            $option = Mage::getModel("configurator/option")->load($this->templateOption->getId());
            $template_id = $option->getTemplateId();
            Mage::helper("configurator")->save(
                $priceListItems,
                $cache_key,
                array("PRODCONF","PRODCONF_TEMPLATE_".$template_id, "PRODCONF_OPTION_".$this->templateOption->getId())
            );
        }
		return $priceListItems;
	}

    /* Selectcombi and Listimagecombi */

    public function getValuePrice($value_id) {
        $value = Mage::getModel("configurator/value")->load($value_id);
        if ($this->getTemplateOption()->hasPricelist($value->getId())) {
            return $this->getCalculatedPrice($value->getId());
        } else {
            return $value->getPrice();
        }
    }

	public function getSelectcombiSimplePricelist() {
		$pricelist = array();
		$values = Mage::getModel("configurator/value")->getCollection();
		$values->addFieldToFilter("option_id", $this->getTemplateOption()->getId());
		foreach ($values as $value) {
			$pricelist[$value->getId()] = array("0" => array("value" => "0", "price" => Mage::app()->getStore()->convertPrice($value->getPrice()), "operator" => ">=", 'simple' => 1));
		}
		return $pricelist;
	}

    /**
     * Overwrite parents method to disable the cache for this request (e.g. my configurations)
     *
     * @return string
     */
    public function toHtmlIgnoreCache()
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/' . $this->getModuleName())) {
            return '';
        }

        $translate = Mage::getSingleton('core/translate');
        /** @var $translate Mage_Core_Model_Translate */
        if ($this->hasData('translate_inline')) {
            $translate->setTranslateInline($this->getData('translate_inline'));
        }

        $this->_beforeToHtml();
        $html = $this->_toHtml();

        if ($this->hasData('translate_inline')) {
            $translate->setTranslateInline(true);
        }

        $html = $this->_afterToHtml($html);

        if ($this->_frameOpenTag) {
            $html = '<'.$this->_frameOpenTag.'>'.$html.'<'.$this->_frameCloseTag.'>';
        }

        self::$_transportObject = new Varien_Object;
        self::$_transportObject->setHtml($html);
        $html = self::$_transportObject->getHtml();

        return $html;
    }

	public function isCombi(){
		$isCombi = "false";
		if($this->getSelectedValue() && $this->templateOption->isCombi()){
			$isCombi = "true";
		}
		return $isCombi;
	}

	public function getPriceInclExclTax($price){
		return Mage::helper("configurator")->getPriceInclExclTax($price);
	}

	public function getOptionValueColor($optionValue){
		$color = "";
		$configHelper = Mage::helper('configurator/config');
		if($configHelper->optionvalueValueIsColor($optionValue->getValue())){
			$color = $optionValue->getValue();
		}
		return $color;

	}
}