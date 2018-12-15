<?php
class EM_Apiios_Model_Api2_Productsinfo_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
    protected $_optionsRender = null;
    public function getOptionsOutput($option){
        if(is_null($this->_optionsRender)){
            $this->_optionsRender = array(
                'default'   =>  'apiios/api2_productsinfo_options_default',
                'select'    =>  'apiios/api2_productsinfo_options_select',
                'text'      =>  'apiios/api2_productsinfo_options_text',
                'date'      =>  'apiios/api2_productsinfo_options_date',
                'file'      =>  'apiios/api2_productsinfo_options_file'
            );
        }
        return Mage::getModel($this->_optionsRender[$this->getGroupOfOption($option->getType())])->setProduct($this->_getProduct())->setOption($option);
    }

    public function getGroupOfOption($type)
    {
        $group = Mage::getSingleton('catalog/product_option')->getGroupByType($type);

        return $group == '' ? 'default' : $group;
    }

	protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $result = array();
		//$id	=	$this->getRequest()->getParam('id');
		$model	=	$this->_getProduct();//Mage::getModel('catalog/product')->load($id);

                $prepareData = $this->_prepareProductForResponse($model);
		$product	=	$model->getData();//print_r($product);exit;
		$product['stock_item']	=	$product['stock_item']->getData();

		$tmp_info['entity_id']	=	$product['entity_id'];
		$tmp_info['type_id']	=	$product['type_id'];
		$tmp_info['name']	=	$product['name'];
		$tmp_info['sku']	=	$product['sku'];
        $tmp_info['url']	=	$model->getProductUrl();

		/*$prices['minimal_price'] 	= $prepareData['minimal_price'];
		$prices['min_price'] 		= $prepareData['min_price'];
		$prices['max_price'] 		= $prepareData['max_price'];
		$prices['price']                = $prepareData['price'];
		$prices['final_price']		= $prepareData['final_price'];
		$prices['tier_price'] 		= $prepareData['tier_price'];

		$tmp_info['prices']	=	$prices;*/
                $tmp_info['price_format'] = Mage::app()->getLocale()->getJsPriceFormat();
                $tmp_info['prices'] = $prepareData['prices'];
        $tmp_info['tier_price'] 		= Mage::helper('apiios')->setStore($this->_getStore())->showTierPrice($this->_getProduct());

		$tmp_info['has_options']	=	$product['has_options'];
		$tmp_info['required_options']	=	$product['required_options'];
		$tmp_info['image']	=	(string)Mage::helper('catalog/image')->init($model, 'image');
		$tmp_info['small_image']	=	(string)Mage::helper('catalog/image')->init($model, 'small_image');
		$tmp_info['thumbnail']		=	(string)Mage::helper('catalog/image')->init($model, 'thumbnail');
		$tmp_info['description']	=	$product['description'];

		$adds	=	array();
		$attributes = $model->getAttributes();
		foreach ($attributes as $attribute) {
			if ($attribute->getIsVisibleOnFront()) {
				$add['label'] = $attribute->getFrontend()->getLabel($model);
				$add['value'] = $attribute->getFrontend()->getValue($model);
				$adds[]	=	$add;
			}
		}
		$tmp_info['additional']	=	$adds;

		$tmp_info['short_description']	=	$product['short_description'];
		$product['media_gallery']['images']	=	array();
		foreach ($model->getMediaGalleryImages() as $image) {
			$product['media_gallery']['images'][]	=	$image->getData();
		}
		$tmp_info['media_gallery']	=	$product['media_gallery'];
		$tmp_info['stock_item']['qty']	=	$product['stock_item']['qty'];
		$tmp_info['stock_item']['use_config_min_qty']	=	$product['stock_item']['use_config_min_qty'];
		$tmp_info['stock_item']['min_qty']	=	$product['stock_item']['min_qty'];
		$tmp_info['stock_item']['use_config_min_sale_qty']	=	$product['stock_item']['use_config_min_sale_qty'];
		$tmp_info['stock_item']['min_sale_qty']	=	$product['stock_item']['min_sale_qty'];
		$tmp_info['stock_item']['use_config_max_sale_qty']	=	$product['stock_item']['use_config_max_sale_qty'];
		$tmp_info['stock_item']['max_sale_qty']	=	$product['stock_item']['max_sale_qty'];
		$tmp_info['stock_item']['is_in_stock']	=	$product['stock_item']['is_in_stock'];
		//$tmp_info['aaaa']	=	$product['aaaa'];

		if($model->getTypeId() == 'grouped'){
			$associatedProducts = $model->getTypeInstance(true)->getAssociatedProducts($model);
			foreach ($associatedProducts as $group_key=>$group_val){
				$groups[$group_key]['entity_id']	=	$group_val->getId();
				$groups[$group_key]['type_id']		=	$group_val->getTypeId();
				$groups[$group_key]['name']			=	$group_val->getName();
				//$groups[$group_key]['price']		=	$group_val->getPrice();
				$groups[$group_key]['prices']		=	Mage::helper('apiios')->showPrice($group_val,true);
			}
			$tmp_info['grouped']	=	$groups;
		}
		elseif($model->getTypeId() == 'configurable'){
            $configurables = $model->getTypeInstance(true)->getConfigurableAttributesAsArray($model);
            $typeOptions = Mage::getModel('apiios/api2_productsinfo_configurable')->setProduct($model);
            $prices = $typeOptions->getConfigurable();
            foreach($configurables as $i => $con){
                if(isset($con['values'])){
                    $values = $con['values'];
                    if(is_array($values)){
                        foreach($values as $index => $value){
                            $configurables[$i]['values'][$index]['prices'] = $prices[$con['attribute_id']][$index];
                        }
                    }
                }
            }
			$tmp_info['configurable'] = $configurables;
            $tmp_info['json_config'] = Mage::helper('core')->jsonDecode($typeOptions->getJsonConfig());
		}
		elseif($model->getTypeId() == 'bundle'){
            $tmp_info['price_config'] = Mage::helper('apiios')->getPriceConfigured($this->_getProduct());
			$optionCollection = $model->getTypeInstance()->getOptionsCollection();
			$selectionCollection = $model->getTypeInstance()->getSelectionsCollection($model->getTypeInstance()->getOptionsIds());
			$options = $optionCollection->appendSelections($selectionCollection);
			foreach( $options as $opt_key=>$opt_value ){
				$bund	=	$opt_value->getData();
				$bund_sub	=	$bund['selections'];
				unset($bund['selections']);
				foreach($bund_sub as $opt_item){
					$bund_items	=	$opt_item->getData();
					$bund_item['entity_id']	=	$bund_items['entity_id'];
					$bund_item['type_id']	=	$bund_items['type_id'];
					$bund_item['option_id']	=	$bund_items['option_id'];
					$bund_item['name']		=	$bund_items['name'];
					$bund_item['selection_id']	=	$bund_items['selection_id'];
					$bund_item['position']		=	$bund_items['position'];
					$bund_item['is_default']	=	$bund_items['is_default'];
					$bund_item['selection_price_type']	=	$bund_items['selection_price_type'];
					$bund_item['selection_price_value']	=	$bund_items['selection_price_value'];
					$bund_item['selection_qty']	=	$bund_items['selection_qty'];
					$bund_item['selection_can_change_qty']	=	$bund_items['selection_can_change_qty'];
					$bund_item['status']		=	$bund_items['status'];
					/*if(isset($bund_items['final_price']))
						$bund_item['final_price']		=	$bund_items['final_price'];
					if(isset($bund_items['tax_percent']))
						$bund_item['tax_percent']		=	$bund_items['tax_percent'];
					if($bund_item['final_price'] == "")	$tmp_pri	=	$bund_items['selection_price_value'];
					else	$tmp_pri	=	$bund_items['final_price'];
					$bund_item['label']		=	Mage::helper('core')->currency($tmp_pri,true,false);*/
                    $bund_item['prices']     =   Mage::getModel('apiios/api2_productsinfo_bundle_option')->setProduct($this->_getProduct())->getSelectionTitlePrice($opt_item);
					$bund['items'][]	=	$bund_item;
				}
				$bundle[]	=	$bund;
			}
			$tmp_info['bundle_item']	=	$bundle;
		}
		elseif($model->getTypeId() == 'downloadable'){
			$_linksPurchasedSeparately = $model->getLinksPurchasedSeparately();
			$down	=	 $model->getTypeInstance(true)->getLinks($model);
			foreach($down as $dow_key=>$dow_val){
				$down	=	$dow_val->getData();
				unset($down['product']);
				
				$download[]	=	$down;
			}
			$tmp_info['downloadable']['purchasedseparately']	=	$_linksPurchasedSeparately;
			$tmp_info['downloadable']['items']	=	$download;			
		}

		$tmp_options	=	array();
		$options = $model->getOptions();
		foreach ($options as $key=>$option) {
			/*$tmp = $option->getData();
			$tmp_values = $option->getValues();
			foreach($tmp_values as $k=>$v){
				$tmp['attr_item'][]	=	$v->getData();
			}*/

			$tmp_options[]	=	$this->getOptionsOutput($option)->getOptionArray();
		}
		$tmp_info['custom_options']	=	$tmp_options;

		$upsell_product = $this->_addProductAttributesAndPrices($model->getUpSellProductCollection()
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())->addAttributeToSort('position', Varien_Db_Select::SQL_ASC)->addStoreFilter());
		$count = count($upsell_product);
		if($count > 0 ){
			$upsell = "";
			$additional = array('thumbnail' => array(
				'width' =>  100,
				'height'=>  100
			));
			foreach($upsell_product->getItems() as $up_key=>$_upsell){
				$this->_setProduct($_upsell);
				//$up	=	$this->getProductsbyId($_upsell->getId());
				//$upsell	=	$this->_prepareProductForResponse($up,$additional);
				$upsell	=	$this->_prepareProductForResponse($_upsell,$additional);
				$upsells[]	=	$upsell;
			}
			$tmp_info['upsell']	=	$upsells;
		}
		$rating = $this->getRating($model);
        $tmp_info['review']['total_reviews_count'] = $rating['total_reviews_count'];
        $tmp_info['review']['rating_summary'] = $rating['rating_summary'];

		$result['info']	=	$tmp_info;
		//print_r($result);exit; 
    	return $result;
    }

	protected function getProductsbyId($id){
		$storeId 	= $this->_getStore()->getId();
		/*$products = Mage::getResourceModel('catalog/product_collection')->setStoreId($storeId)->addStoreFilter();
        $products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $products = $this->_addProductAttributesAndPrices($products)
			->addAttributeToFilter('entity_id',$id);
		return $products->getFirstItem();*/
        return Mage::getModel('catalog/product')->setStoreId($storeId)->load($id);
	}
}
?>