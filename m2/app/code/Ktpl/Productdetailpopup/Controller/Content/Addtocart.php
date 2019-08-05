<?php

namespace Ktpl\Productdetailpopup\Controller\Content;


class Addtocart extends \Magento\Framework\App\Action\Action 
{

	/**
      * @var \Magento\Checkout\Model\Cart
      */
	protected $cart;
     /**
      * @var \Magento\Catalog\Model\Product
      */
    protected $productFactory;
    protected $_productRepository;
    protected $_pageFactory;
	protected $_storeManager;
	protected $_configurableProTypeModel;
    protected $product;
    protected $formKey;

    public function __construct(
     	\Magento\Framework\App\Action\Context $context,
     	\Magento\Framework\View\Result\PageFactory $resultPageFactory,
     	\Magento\Catalog\Model\ProductFactory $productFactory,
     	\Magento\Catalog\Model\Product $product,
     	\Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\View\Result\PageFactory $pageFactory,
     	\Magento\Catalog\Model\ProductRepository $productRepository,
     	\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProTypeModel,
     	\Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
     	$this->_productRepository = $productRepository;
     	$this->resultPageFactory = $resultPageFactory;
     	$this->_pageFactory = $pageFactory;
		$this->_storeManager = $storeManager;
     	$this->cart = $cart;
     	$this->productFactory = $productFactory;
     	$this->_configurableProTypeModel = $configurableProTypeModel;
     	$this->product = $product;
        $this->formKey = $formKey;
     	parent::__construct($context);
    }
    public function execute()
    {

        try {
            $notaddtocart = [];
            if (!empty($_POST['item'])) {
              $totalitms = $_POST['item'];
            }
            if (!empty($_POST['r_pro'])) {
              $relatedproduct = $_POST['r_pro'];
            }
            if (!empty($_POST['product'])) {
              $sku = $_POST['product'];
            }
            $cartvalidate = false;
            $productAttributeOptions = array();
            $productData = [];
            $storeId = $this->_storeManager->getStore()->getId();
            if (!empty($totalitms)) {
              foreach ($totalitms as $productData) {
                if(!$productData['qty'])
                    continue;
                $configButton = false;
                if ($this->_objectManager->create('Magento\Catalog\Model\Product')->getIdBySku(trim($sku))) {

                    $_product = $this->_productRepository->get(trim($sku), false, null, true)->setData('store_id', $storeId);

                    $productData['product'] = $_product->getId();

                    $ifOptionsAvailable = $_product->getHasOptions();
                    $productType = $_product->getTypeId();

                    $this->cart->addProduct($_product, $productData);
                    $cartvalidate = true;
                } else {
                    array_push($notaddtocart, $sku);
                }
              }
          }
          if (!empty($relatedproduct)) {
            foreach ($relatedproduct as $key => $r_productData) {
                $qty = $r_productData['qty'];
                $id = $r_productData['id'];
                $sku = $r_productData['sku'];
                $price = $r_productData['price'];
                if(!$qty)
                    continue;
                $params = array(
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $id, //product Id
                    'qty'   => $qty, //quantity of product
                    'sku'   => $sku,
                    'price' => $price
                );      

                $_product = $this->_productRepository->getById($id);
                
                if ($_product) {
                    $this->cart->addProduct($_product, $params);
                    $cartvalidate = true;
                }
            }
          }
            $this->cart->save();
            $this->cart->setCartWasUpdated(true);

            $nottocart = implode(';', $notaddtocart);
            if ($cartvalidate) {
                if ($notaddtocart) {
                    $this->messageManager->addSuccess(__('Added to cart successfully.'));
                    $this->messageManager->addError(__('Product not added to cart with SKU -' . $nottocart . ''));
                } else {
                    $this->messageManager->addSuccess(__('Added to cart successfully.'));
                }
            } else {
                $this->messageManager->addError(__('Product not added to cart with SKU -' . $nottocart . ''));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException(
                    $e, __('%1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went Wrong. Please try again.'.$e->getMessage()));
        }
        $this->_redirect($this->_redirect->getRefererUrl());
        //$this->_redirect('checkout/cart');
    }
}