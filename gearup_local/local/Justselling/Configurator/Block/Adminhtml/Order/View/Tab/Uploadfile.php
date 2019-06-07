<?php
class Justselling_Configurator_Block_Adminhtml_Order_View_Tab_Uploadfile
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_chat = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('configurator/order/view/tab/file.phtml');
    }

    public function getTabLabel() {
        return $this->__('Files');
    }

    public function getTabTitle() {
        return $this->__('Files');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function hasSvgFiles(Mage_Sales_Model_Order $order) {
        /** @var $files Justselling_Configurator_Model_Mysql4_Vectorgraphics_File_Collection */
        $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
            ->addFieldToFilter("order_id",$order->getId())
            ->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_ORDER);
        if ($files->getSize()) {
            return true;
        }

        return false;
    }

    protected function customImageFolder() {
        $mediaFolder = Mage::getBaseDir('media');
        $configuratorFolder = $mediaFolder . DS . "configurator";
        if (!file_exists($configuratorFolder)) {
            mkdir($configuratorFolder);
        }
        $uploadFolder = $configuratorFolder . DS . "uploads";
        if (!file_exists($uploadFolder)) {
            mkdir($uploadFolder);
        }
        return $uploadFolder;
    }

    public function getCustomImageUrl($optionId, $jstemplateId) {
        $mediaFolder = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $configuratorFolder = $mediaFolder . DS . "configurator";
        if (!file_exists($configuratorFolder)) {
            mkdir($configuratorFolder);
        }
        $uploadFolder = $configuratorFolder . DS . "uploads";
        if (!file_exists($uploadFolder)) {
            mkdir($uploadFolder);
        }

        $filename = "combinedimage_".$optionId."_".$jstemplateId.".png";
        $img = $uploadFolder. "/". $filename;

        return $img;
    }

    public function hasCustomImage($optionId, $jstemplateId) {
        $hasCustomImage = false;

        $filename = "combinedimage_".$optionId."_".$jstemplateId.".png";
        $customImage = $this->customImageFolder() . "/" . $filename;
        if (file_exists($customImage)) {
            $hasCustomImage = true;
        }
        return $hasCustomImage;
    }

    public function getSvgFiles(Mage_Sales_Model_Order $order) {
        /** @var $files Justselling_Configurator_Model_Mysql4_Vectorgraphics_File_Collection */
        $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
            ->addFieldToFilter("order_id",$order->getId())
            ->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_ORDER);
        return $files;
    }

    public function getDownloadUrl(Justselling_Configurator_Model_Vectorgraphics_File $file) {
        if (is_object($file)) {
            $url = Mage::helper("adminhtml")->getUrl("configurator/adminhtml_vectorgraphics/index/",
                array(
                    "key" => Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_vectorgraphics","index")
                ));
            $url .= "id/".$file->getId();
            return $url;
        }
        return false;
    }

    public function getFileContent(Justselling_Configurator_Model_Vectorgraphics_File $file) {
        if (is_object($file)) {
            $content = "";
            foreach (unserialize($file->getContent()) as $line) {
                $content .= $line." ";
            }
            return $content;
        }
        return "";
    }

    public function getProductName(Justselling_Configurator_Model_Vectorgraphics_File $file) {
        if (is_object($file)) {
            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel("catalog/product")->load($file->getProductId());
            return $product->getName();
        }
        return "";
    }

    public function getOrder(){
        return Mage::registry('current_order');
    }
    
    public function getOptionname($id){
    	$option = Mage::getModel('configurator/option')->load($id);
    	return $option->getTitle();
    	
    }
    
    public function getOrderItemPosition($orderId, $orderItemId){
    	$orderItems = Mage::getModel('sales/order_item')->getCollection();
    	$orderItems->addFieldToFilter('order_id', $orderId);
    	$position = 1;
    	foreach ($orderItems as $orderItem){
    		if($orderItem->getItemId() == $orderItemId){
    			break;
    		}
    		$position++;
    	}
    	
    	return $position;
    	
    }
}  
?>