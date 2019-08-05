<?php
namespace Ktpl\General\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_resultFactory;
	private $productRepository;
	protected $scopeConfig;
	protected $promoCartHelper;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Amasty\Promo\Helper\Cart $promoCartHelper,
		\Magento\SalesRule\Model\Rule $rule,
		\Amasty\Promo\Model\Rule $amastyRule,
		\Ktpl\General\Helper\Data $ktplHelper
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_resultFactory = $resultFactory;
		parent::__construct($context);
		$this->productRepository = $productRepository;
		$this->formKey = $formKey;
		$this->cart = $cart;
		$this->scopeConfig = $scopeConfig;
		$this->promoCartHelper = $promoCartHelper;
		$this->rule = $rule;
		$this->amastyRule = $amastyRule;
		$this->ktplHelper = $ktplHelper;


	}

	public function execute()
	{	
		
			$responseResult = [];
			 if ($data = $this->getRequest()->getParams()){

			 	if($this->getRequest()->getParam('optionsku')){
					
					$priceHtml =  $this->ktplHelper->getProductPrice($this->getRequest()->getParam('optionsku'));
			 		$responseResult = array('type'=>'success','priceHtml'=> $priceHtml ); 
			 		$response = $this->resultFactory
						->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
						->setData($responseResult);

					return $response; 
			 	}

				try {
					
					$sku = $this->getRequest()->getParam('product_sku');
					$ruleId = $this->getRequest()->getParam('ruleId');

					$product = $this->productRepository->get($sku);

				} catch (\Magento\Framework\Exception\NoSuchEntityException $e){
					$responseResult = array('type'=>'error','message' => __("Product doesn't exist."));
					$response = $this->resultFactory
					->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
					->setData($responseResult);

					return $response;      
				}
				
				if($product->getTypeId() == "simple" && $product->getIsSalable() && !$product->canConfigure())
				{

					$productId =$product->getId();
					$params = array(
						'form_key' => $this->formKey->getFormKey(),
						'product'  => $productId, //product Id
						'qty'      => 1 //quantity of product                
						);
					
					$this->cart->addProduct($product, $params);
					try{
						$this->cart->save();	

/* gift */ 		if(!empty($ruleId))
				{ 				
	                $item = [];
	                $item['product_id'] = $product->getId();

				    $loadRule = $this->rule->load($ruleId);
					$ruleData = $this->amastyRule->loadBySalesrule($loadRule);
					$promoItem = explode(',', $ruleData->getSku());
					if(!empty($promoItem)) {
						$promoSku = $promoItem[0];
					    $productgift = $this->productRepository->get($promoSku);
		                $qty = 1;
		                $params = $item;
		                    $requestOptions = array_intersect_key($params, array_flip([
		                        'super_attribute', 'options', 'super_attribute', 'links'
		                    ]));
		                $this->promoCartHelper->addProduct(
		                        $productgift,
		                        $qty,$ruleId,$requestOptions,$productgift->getPrice()
		                    );
		                $this->promoCartHelper->updateQuoteTotalQty(true); 
		            }
	            }
/* gift */

					}
					catch(\Magento\Framework\Exception\NoSuchEntityException $e){
						$responseResult = array('type'=>'error','message' => $e->getMessage());
						$response = $this->resultFactory
						->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
						->setData($responseResult);

						return $response;     
					}
					

					$responseResult = array('type'=>'success','message'=> __('You added %1 to your cart.',$product->getName()) ); 

				}
				elseif($product->getTypeId() == "configurable")
				{
				
					$params = array(
						'form_key' => $this->formKey->getFormKey(),
						'product'  => $product->getId(), //product Id
						'qty'      => 1 //quantity of product                
						);
					
					$attributeid = $this->getRequest()->getParam('getattributeId');
					$optionId = $this->getRequest()->getParam('optionidselected');
					
					$params['super_attribute'] = array(
                		$attributeid => $optionId
            		); // attributeid => option id 
					
					$this->cart->addProduct($product, $params);
					
					try{
						$this->cart->save();	

/* gift */		if(!empty($ruleId))
				{ 				
	                $item = [];
	                $item['product_id'] = $product->getId();

				    $loadRule = $this->rule->load($ruleId);
					$ruleData = $this->amastyRule->loadBySalesrule($loadRule);
					$promoItem = explode(',', $ruleData->getSku());
					if(!empty($promoItem)) {
						$promoSku = $promoItem[0];

		                $productgift = $this->productRepository->get($promoSku);
		                $qty = 1;
		                $params = $item;
		                    $requestOptions = array_intersect_key($params, array_flip([
		                        'super_attribute', 'options', 'super_attribute', 'links'
		                    ]));
		                $this->promoCartHelper->addProduct(
		                        $productgift,
		                        $qty,$ruleId,$requestOptions,$productgift->getPrice()
		                    );
		                $this->promoCartHelper->updateQuoteTotalQty(true); 
		            }
	            }
/* gift */
					}catch(\Magento\Framework\Exception\NoSuchEntityException $e){
						$responseResult = array('type'=>'error','message' => $e->getMessage());
						$response = $this->resultFactory
						->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
						->setData($responseResult);

						return $response;     
					}
					
					$responseResult = array('type'=>'success','message'=> __('You added %1 to your cart.',$product->getName()));
				}elseif($product->getTypeId() == "bundle")
				{
					$params = array(
						'form_key' => $this->formKey->getFormKey(),
						'product'  => $product->getId(), //product Id
						'qty'      => 1 //quantity of product                
						);
					/* */
					
    				 
					$selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
							$product->getTypeInstance(true)->getOptionsIds($product), $product
						);
 
	    $bundleOptions = [];
		foreach ($selectionCollection as $selection) 
		{
            $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
            break;
        }

        	
					$params['bundle_option'] = $bundleOptions;

					$this->cart->addProduct($product, $params);

					try{
						$this->cart->save();	

/* gift */		if(!empty($ruleId))
				{ 				
	                $item = [];
	                $item['product_id'] = $product->getId();

				    $loadRule = $this->rule->load($ruleId);
					$ruleData = $this->amastyRule->loadBySalesrule($loadRule);
					$promoItem = explode(',', $ruleData->getSku());
					if(!empty($promoItem)) {
						$promoSku = $promoItem[0];

		                $productgift = $this->productRepository->get($promoSku);
		                $qty = 1;
		                $params = $item;
		                    $requestOptions = array_intersect_key($params, array_flip([
		                        'super_attribute', 'options', 'super_attribute', 'links'
		                    ]));
		                $this->promoCartHelper->addProduct(
		                        $productgift,
		                        $qty,$ruleId,$requestOptions,$productgift->getPrice()
		                    );
		                $this->promoCartHelper->updateQuoteTotalQty(true); 
		            }
	            }
/* gift */
					}catch(\Magento\Framework\Exception\NoSuchEntityException $e){
						$responseResult = array('type'=>'error','message' => $e->getMessage());
						$response = $this->resultFactory
						->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
						->setData($responseResult);

						return $response;     
					}
					
					$responseResult = array('type'=>'success','message'=> __('You added %1 to your cart.',$product->getName()));

				}  // close bundle if
				
				$response = $this->resultFactory
				->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
				->setData($responseResult);

				return $response;

			 } /* */// data if close;
	}
}
