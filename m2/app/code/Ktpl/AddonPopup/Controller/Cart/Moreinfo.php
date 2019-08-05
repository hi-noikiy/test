<?php
namespace Ktpl\AddonPopup\Controller\Cart;


class Moreinfo extends \Magento\Framework\App\Action\Action 
{

	protected $_pageFactory;
    protected $_productRepository;
    protected $_storeManager;
    protected $jsonHelper;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Catalog\Model\ProductRepository $productRepository, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Framework\View\Result\PageFactory $pageFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper) 
    {
        $this->_pageFactory = $pageFactory;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        return parent::__construct($context);
    }

    public function execute() {
        $product_id = $this->getRequest()->getParam('id');
        $product = $this->_productRepository->getById($product_id);
        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $description = $product->getData('description');
        $image = $url . 'catalog/product' . $product->getImage();
        $sku = $product->getData('sku');
        $name = $product->getData('name');
        $data = $sku . "/" . $image . "/" . $description;
        $final_description = "<div class='products-name'>".$name."</div>".$description;
        $arrayyyy = array(
            'p_id' => $product_id,
            'sku' => $sku,
            'image' => $image,
            'name' => $name,
            'description' => $final_description,
        );

        echo $this->jsonHelper->jsonEncode($arrayyyy); exit;
    }

}