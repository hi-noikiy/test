<?php

namespace Ktpl\Productdetailpopup\Controller\Content;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

class Index extends \Magento\Framework\App\Action\Action {

    protected $_productRepository;
    protected $_storeManager;
    protected $jsonHelper;
    private $_product;
    protected $stockRegistry;
    protected $ConfigurableBlock;
    protected $resultPageFactory;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Catalog\Model\ProductRepository $productRepository, 
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Json\EncoderInterface $jsonEncoder,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Ktpl\Productdetailpopup\Block\Product\View\Type\Configurable $ConfigurableBlock,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            Product $product
    ) 
    {
        $this->jsonEncoder = $jsonEncoder;
        $this->ConfigurableBlock = $ConfigurableBlock;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_product = $product;
        return parent::__construct($context);
    }

    public function execute() {
        $product_id = $this->getRequest()->getParam('id');
        $product = $this->_productRepository->getById($product_id);

        $result = $this->resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        
        $block = $resultPage->getLayout()
                ->createBlock('Ktpl\Productdetailpopup\Block\Product\View\Type\Configurable')
                ->setTemplate('Ktpl_Productdetailpopup::wholesaler/detailpopup.phtml')
                ->setCustomProduct($product)
                ->toHtml();

        $result->setData(['output' => $block]);
        return $result;




        /*$sku = $product->getSku();
        $name = $product->getName();
        $description = $product->getDescription();
        $color = [];
        $id = [];
        if($product->getData('type_id') == 'configurable'){
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('module_custom_customlayout');
            echo "<pre>";
            print_r($resultPage);
            exit;
            $productdata = $this->ConfigurableBlock->getJsonConfig1($product);
            $productdata = json_decode($productdata);
            
            $product_id = $productdata->productId;
            $productdata = $productdata->attributes;
            foreach ($productdata as $options) {
                $attribute_id       = $options->id;
                $attribute_code     = $options->code;
                $attribute_label    = $options->label;
                $selected_product   = $options->options;
                foreach ($selected_product as $product_data) {
                    $option_label[$product_data->id] = $product_data->label;
                    $id[] = $product_data->products[0];
                }
            }
        }

        $arrayyyy = array(
            'p_id' => $product_id,
            'sku' => $sku,
            'name' => $name,
            'description' => $description,
            'color' => $option_label,
            'subproduct_id' => $id,
        );
        echo $this->jsonEncoder->encode($arrayyyy); exit;*/
    }

}
?>