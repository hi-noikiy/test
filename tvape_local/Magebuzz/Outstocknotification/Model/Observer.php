<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Outstocknotification_Model_Observer extends Mage_Core_Model_Abstract {
  private $bcc;
  private $productName;
  private $productUrl;
  private $prodcutImg;
  private $siteLink;
  private $productDescr;
  private $storeName;
  const XML_PATH_EMAIL_SENDER = 'outstocknotification/outofstock_email/outofstock_sender_email_identity';

  public function sendMailToNotifiedToCustomer($observer) {
    $enableOutOfStock   = Mage::getStoreConfig('outstocknotification/general/module_enable'); 
    $oldProduct            = $observer->getProduct(); 
    $oldStock          = $oldProduct['is_in_stock']; 
    $product_id         = $oldProduct->getId();     
    $product = Mage::getModel('catalog/product')->load($product_id);  
    $isInStock = $product->getIsInStock();    
    $enableOutOfStock   = intval($enableOutOfStock); 
    $productUrl         = $product->getProductUrl();    
    $status             = $oldProduct->getStatus();

    if ($oldProduct->_isObjectNew) {
      return 1;
    }
    if (!$enableOutOfStock) {  
      return 1; 
    }
    if (!$oldStock && $isInStock) {     
      $this->storeName    = Mage::getStoreConfig("general/store_information/name");
      $this->productDescr = $product->getShortDescription();
      $product_id         = $product->getId();      
      $this->productUrl         = $product->getProductUrl();
      $this->productName        = $product->getName();
      $this->prodcutImg = Mage::helper('catalog/product')->getImageUrl($product) ;          
      if ($status ) {

        $productId          = $oldProduct->getId();
        $producttype        = $oldProduct->getTypeId();
        $mailFunCallOrNot   = $this->isProductInNotifiyList($productId);  

        if ($mailFunCallOrNot) {
          $sendEmailVal   = $this->_sendEmail($this->bcc);
          $this->updateMailAndStatusOfNotifiy($productId); 
        }
      } else {
        return false;  
      }
    } 
  }

  private function isProductInNotifiyList($productId){

    $this->bcc = array();
    $mailStaus = '0';

    $collection = Mage::getModel('productalert/stock')->getCollection()
    ->addFieldToFilter('product_id',$productId)
    ->addFieldToFilter('status', '0');    
    $collection->getSelect()->group( array("email") );
    $isArray = $collection->getSize();
    if ($isArray) {
      foreach ($collection as $productlist) {
        $this->bcc[] = $productlist->getEmail();
      }
      return 1;
    } else {
      return 0;
    }
  }

  public function _sendEmail($to) {
    if (!$to)
      return;

    $sendTo = array();

    foreach ($to as $recipient) {
      if (is_array($recipient)) {
        $sendTo[] = $recipient['email'];
      } else {
        $sendTo[] =$recipient;
      }
    }
    $this->productDescr = substr($this->productDescr, 0, 420);
    if (strlen($this->productDescr) > 420) {
      $this->productDescr .= '...';
    }

    $emailTemplateVariables = array();
    $emailTemplateVariables['productName'] = $this->productName;
    $emailTemplateVariables['productUrl'] = $this->productUrl;      
    $emailTemplateVariables['productImg'] = $this->prodcutImg;
    $emailTemplateVariables['storeName'] = $this->storeName;
    $emailTemplateVariables['siteLink'] = $this->siteLink;
    $emailTemplateVariables['productDesc'] = $this->productDescr;

    $marchentNotificationMailId = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
    //$senderMailId   = Mage::getStoreConfig("trans_email/ident_$marchentNotificationMailId/email");
    //$senderName     = Mage::getStoreConfig("trans_email/ident_$marchentNotificationMailId/name");
    $templeId       = Mage::getStoreConfig('outstocknotification/outofstock_email/outofstock_template');      
   
    $translate = Mage::getSingleton('core/translate');                 
    foreach ($sendTo as $mail) {  
      $translate->setTranslateInline(false);
      $storeId = Mage::app()->getStore()->getId();    
      try {
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
        //->setReplyTo($senderMailId)
        ->sendTransactional(
        $templeId,
        $marchentNotificationMailId,
        $mail,
        null,
        $emailTemplateVariables
        );

        if (!$mailTemplate->getSentSuccess()) {
          throw new Exception();
        }      
      }
      catch(Exception $e) {      
      }
      $translate->setTranslateInline(true);
    }

  }

  private function updateMailAndStatusOfNotifiy($productId){
    $deleteNotify = (int) Mage::getStoreConfig('outstocknotification/outofstock_email/delete_outofstock_mail');
    $resource = Mage::getSingleton('core/resource');
    $write = $resource->getConnection('core_write');  
    $tableName = $resource->getTableName('product_alert_stock');
    if ($deleteNotify) {
      $where ="product_id = $productId";
      $write->delete($tableName, $where);
    } else {     
      $deleteNotify = 1;
      $data = array('status'=>$deleteNotify ,'send_date'=>now(),'send_count'=>1);
      $where ="product_id = $productId";
      $write->update($tableName, $data, $where);
    }      

  }

  public function addTabAlertStock($observer){

    $block = $observer->getEvent()->getBlock(); 
    $enableModule = Mage::helper('outstocknotification')->enableModule();
    if($enableModule){
      if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs)
      {
        if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type'))
        {
          $block->addTab('out_stock_notification', array(
          'label'     => Mage::helper('outstocknotification')->__('Product Out Of Stock Alerts'),
          'content'   => $block->getLayout()->createBlock('outstocknotification/adminhtml_productalert_gridtab')->toHtml())
          );
        }
      }
    }
  }
  protected function _getRequest()
  {
    return Mage::app()->getRequest();
  }
}