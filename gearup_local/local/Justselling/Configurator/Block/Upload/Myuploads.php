<?php
class Justselling_Configurator_Block_Upload_Myuploads extends Mage_Customer_Block_Account_Dashboard
{

    public function __construct()
    {
    	Mage::Log("Fileuploader Block construct");
        parent::__construct();
        $this->setTemplate('configurator/upload/myuploads.phtml');
    }

	public function getMyUploads() {
		$customer_id = Mage::getSingleton('customer/session')->getCustomerId();
		Mage::Log("customer=".$customer_id);
		if ($customer_id) {
			$uploads = Mage::getModel("configurator/upload")->getCollection()->getByCustomerId($customer_id);
			return $uploads;
		}
	}
	
	public function getOrderState($upload) {
		if ($upload && $upload->getOrderId()) {
			$order = Mage::getModel("sales/order")->load($upload->getOrderId());
			return $order->getState();
		}
		return false;
	}

    public function getUploadImage($filename) {
        $parts = explode(".",$filename);
        $extension = $parts[sizeof($parts)-1];
        if (in_array($extension, array("gif","jpeg","jpg","png","tiff","tif"))) {
            if ($extension == "jpg") $extension="jpeg";
            if ($extension == "tif") $extension="tiff";
            return $this->getSkinUrl("images/justselling/image/".$extension.".png");
        }
        if (in_array($extension, array("doc","docx","pdf","psd","zip","vnd"))) {
            if ($extension == "docx") $extension="msword";
            if ($extension == "doc") $extension="msword";
            if ($extension == "psd") $extension="x-photoshop";
            if ($extension == "vnd") $extension="msword";
            return Mage::getDesign()->getSkinUrl('images/justselling/application/' .$extension .'.png');
        }
        if (in_array($extension, array("xml"))) {
            return Mage::getDesign()->getSkinUrl('images/justselling/text/' .$extension .'.png');
        }
        return Mage::getDesign()->getSkinUrl('images/justselling/file.png');
    }
}