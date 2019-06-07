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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_FontController extends Mage_Adminhtml_Controller_Action
{	
	
	protected $_response = null;

    protected function getFontBaseFolder() {
        $baseFolder = Mage::getBaseDir('media') . DS . 'configurator' . DS . 'font';
        if (!file_exists($baseFolder)) {
            mkdir($baseFolder);
        }
        return $baseFolder;
    }


    /*
     * Will convert ttf font to other web-font formats using remote service
     * on justelling.de
     */
    protected function convertFont($font, $format) {
        $baseUri = "http://www.justsellingapp.com/fontservice/service/font";
        $sourceFontUri = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "/". $font->getFontFile();
        $uri = $baseUri . "?f=".urlencode($format)."&t=".urlencode($sourceFontUri);

        $http = new Zend_Http_Client ();
        $http->setUri($uri);
        $http->setMethod(Zend_Http_Client::GET);
        $fontData = $http->request()->getBody();

        $fontBaseolder = $this->getFontBaseFolder();
        $fontName = basename($font->getFontFile());
        $fontTargetName = str_replace(".ttf", ".".$format, $fontName);
        $fileHandle = fopen($fontBaseolder . DS . $fontTargetName, "w+");
        fwrite($fileHandle,$fontData);
        fclose($fileHandle);

        return $fontTargetName;
    }

	public function indexAction()
	{
		$this->loadLayout()
			->_addContent( $this->getLayout()->createBlock('configurator/adminhtml_font') )
			->renderLayout();
	}
	
	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock("importedit/adminhtml_font_grid")->toHtml()
		);
	}
	
	public function editAction()
	{
		$fontId = $this->getRequest()->getParam("id");		
		$configuratorModel = Mage::getModel("configurator/font")->load($fontId);
		
		if( $configuratorModel->getId() || $fontId == 0 )
		{
			Mage::register("font_data", $configuratorModel);
			
			$this->loadLayout();
			$this->_setActiveMenu("configurator/font");
			
			$this->_addBreadcrumb( Mage::helper("adminhtml")->__("Font Manager"),  Mage::helper("adminhtml")->__("Font Manager"));
			
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
			
			$this->_addContent( $this->getLayout()->createBlock("configurator/adminhtml_font_edit") )
				->_addLeft( $this->getLayout()->createBlock("configurator/adminhtml_font_edit_tabs") );
			
			$this->renderLayout();			
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError( Mage::helper("adminhtml")->__("Font does not exist") );
			$this->_redirect("*/*/");
		}
	}
	
	public function newAction()
	{
		$this->_forward('edit');
	}
	
	
	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id',false);
		
		if( $id ) {			
			try {
				$templateModel = Mage::getModel("configurator/font")->load($id);
				$templateModel->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess( Mage::helper("adminhtml")->__("Font was successfully deleted") );
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		
		$this->_redirect("*/*/");
	}
	
	
	public function saveAction()
	{
		$id = $this->getRequest()->getParam('id',false);
		
		if( $this->getRequest()->getPost() )
		{
			try {
				$post = $this->getRequest()->getPost();
				$fontModel = Mage::getModel("configurator/font");
				
				$font_file = 0;
				if(isset($_FILES['font_file']['name']) and (file_exists($_FILES['font_file']['tmp_name']))) {		
					try {
						$uploader = new Varien_File_Uploader("font_file");
						$uploader->setAllowedExtensions(array('ttf'));
						$uploader->setAllowRenameFiles(true);
						$path = $this->getFontBaseFolder();
						$result = $uploader->save($path);										
						$font_file = "configurator/font/".$result['file'];
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						Mage::getSingleton('adminhtml/session')->setConfiguratorData($this->getRequest()->getPost());
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						return;					
					}
				} else {       
    				if(isset($post['font_file']['delete']) && $post['font_file']['delete'] == 1) {
        				$font_file = "";
    				} else {
        				unset($font_file);
        			}
				}
				
				$fontModel->setId( $this->getRequest()->getParam("id"))
					->setTitle( $post["title"] )
					->setFontType( $post["font_type"] )
					->setOrder( $post["order"] );
					
				if (isset($font_file)) {
					$fontModel->setFontFile($font_file);
				}
									
				$fontModel->save();		
				$id = $fontModel->id;

                $this->convertFont($fontModel, 'woff');
                $this->convertFont($fontModel, 'svg');
                $this->convertFont($fontModel, 'eot');
				
				Mage::getSingleton("adminhtml/session")->addSuccess( Mage::helper("adminhtml")->__("Font was successfully saved") );
				Mage::getSingleton("adminhtml/session")->setConfiguratorData(false);
				
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setConfiguratorData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		
		if( $id ) {
			$this->_redirect('*/*/edit', array('id' => $id));
		} else {
			$this->_redirect("*/*/");
		}
	}
	
}