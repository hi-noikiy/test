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
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_AdminController extends Mage_Adminhtml_Controller_Action
{
	var $_form;

	protected function _isAllowed()
    {
        /** @var Mage_Adminhtml_Model_Session $adminSession */
        $adminSession = Mage::getSingleton('admin/session');

        return $adminSession->isAllowed('configurator/templatemanager') || $adminSession->isAllowed('configurator/fontmanagement');
    }
	public function indexAction()
	{
		$this->loadLayout()
		->_addContent( $this->getLayout()->createBlock('configurator/adminhtml_configurator') )
		->renderLayout();
	}

	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock("importedit/adminhtml_configurator_grid")->toHtml()
		);
	}

	public function editAction()
	{
		$configuratorId = $this->getRequest()->getParam("id");
		$configuratorModel = Mage::getModel("configurator/template")->load($configuratorId);

		if( $configuratorModel->getId() || $configuratorId == 0 )
		{
			Mage::register("configurator_data", $configuratorModel);

			$this->loadLayout();
			$this->_setActiveMenu("configurator/templates");

			$this->_addBreadcrumb( Mage::helper("configurator")->__("Template Manager"),  Mage::helper("configurator")->__("Template Manager"));

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this->_addContent( $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit") )
			->_addLeft( $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tabs") );

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError( Mage::helper("configurator")->__("Template does not exist") );
			$this->_redirect("*/*/");
		}
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function saveAction()
	{
		$id = $this->getRequest()->getParam('id',false);
		$isAjax = $this->getRequest()->getParam('isAjax',false);
		$output = array();

		if( $this->getRequest()->getPost() )
		{
			try {
                if ($id != null) {
                    Mage::helper('configurator/blacklistCleaning')->clean($id);
                }

				$post = $this->getRequest()->getPost();
				$templateModel = Mage::getModel("configurator/template");

				if (!empty($post['template_image'])) {
					$targetFileName = 'templateimage-' .$post['template_image'];
					$mediaFolder = Mage::getBaseDir('media');
					$targetFolder = 'configurator' .DS .$id;

					if (strpos($targetFileName,'configurator/') !== false) {
						$template_image =   $post['template_image'];
					}else{
						$template_image = $targetFolder .DS .$targetFileName;
					}

					try {
						$tempFolder = Mage::getBaseDir('media') . '/tmp/upload/admin';
						$tempFile = rtrim($tempFolder, '/') . '/' . $post['template_image'];

						if (file_exists($tempFile)) {
							Mage::helper('configurator/upload')->createAllDirectoriesFromPath($targetFolder);

							$targetFile = rtrim($mediaFolder, '/') . DS .$template_image;
							rename($tempFile, $targetFile);
							$templateModel->setTemplateImage($template_image);
						}
					} catch (Exception $e) {
						$templateModel->setTemplateImage(null);
					}
				} else {
					$templateModel->setTemplateImage(null);
				}


				if (!empty($post['base_image'])) {
					$targetFileName = 'baseimage-' .$post['base_image'];
					$mediaFolder = Mage::getBaseDir('media');
					$targetFolder = 'configurator' .DS .$id;

					if (strpos($targetFileName,'configurator/') !== false) {
						$template_image =   $targetFolder .DS .str_replace('configurator/', '', $post['base_image']);
					}else{
						$template_image = $targetFolder .DS .$targetFileName;
					}
					try {
						$tempFolder = Mage::getBaseDir('media') . '/tmp/upload/admin';
						$tempFile = rtrim($tempFolder, '/') .DS . $post['base_image'];
						if (file_exists($tempFile)) {
							Mage::helper('configurator/upload')->createAllDirectoriesFromPath($targetFolder);

							$targetFile = rtrim($mediaFolder, '/') .DS . $template_image;
							rename($tempFile, $targetFile);
							$templateModel->setBaseImage($template_image);
						}
					} catch (Exception $e) {
						$templateModel->setBaseImage(null);
					}
				} else {
					$templateModel->setBaseImage(null);
				}

				$design = array();
                if (isset($post["more_info_design"]))
				    $design ["more_info_design"] = $post["more_info_design"];
                if (isset($post["blacklist_mode"]))
                    $design ["blacklist_mode"] = $post["blacklist_mode"];
                if (isset($post["blacklist_children_auto"]))
                    $design ["blacklist_children_auto"] = $post["blacklist_children_auto"];
                if (isset($post["blacklist_text_display"]))
                    $design ["blacklist_text_display"] = $post["blacklist_text_display"];
                if (isset($post["text2image_singleline"]))
                    $design ["text2image_singleline"] = $post["text2image_singleline"];
                if (isset($post["group_switch_before_validate"]))
                    $design ["group_switch_before_validate"] = $post["group_switch_before_validate"];

				$templateModel->setId( $this->getRequest()->getParam("id"))
					->setTitle( $post["title"] )
					->setHeadline( $post["headline"] )
					->setAltCheckout( $post["alt_checkout"] )
					->setCombinedProductImage( $post["combined_product_image"] )
					->setJpegQuality( $post["jpeg_quality"] )
                    ->setCombinedAdaptSize( $post["combined_adapt_size"])
                    ->setCombinedAdaptFactor( $post["combined_adapt_factor"])
                    ->setFontAdaptFactor( $post["font_adapt_factor"])
					->setOptionValuePrice( $post["option_value_price"] )
					->setOptionValuePriceZero( $post["option_value_price_zero"])
					->setGroupLayout( $post["group_layout"])
					->setGroupEnumerate( $post["group_enumerate"])
					->setSvgExport($post["svg_export"])
					->setMassFactor($post["mass_factor"])
					->setDesign(serialize($design));

				$templateModel->save();
				$id = $templateModel->id;
				if( isset($post["template"]["groups"]) ) {
					$groupModel  = Mage::getModel("configurator/optiongroup");
					$groupModel->setTemplate($templateModel);
					$groupModel->saveTemplateGroups($post["template"]["groups"]);
				}
				if( isset($post["template"]["subsections"]) ) {
					$resource = Mage::getSingleton('core/resource');
					$write = $resource->getConnection('core_write');
					$table = $resource->getTableName('configurator_subsection');
					
					foreach($post['template']['subsections'] as $key=> $subsec){
						if($subsec['is_delete'] ){
							$write->delete($table,['id = ?' => $key] );
						} else { 	
							$select = $write->select()
    							->from(['tbl' => $table], ['id'])
    							->where('id=?',"{$key}");
							$results = $write->fetchOne($select);
							if($results){
								$write->update( $table, 
						    	['template_id' => $id, 'option_id' => $subsec['option_id'],'sortorder' => $subsec['sortorder'],'subtitle' => $subsec['subtitle']],
						    	['id = ?' => $key] );	
							} else{
								$write->insert( $table, 
						    		['id' => $key,'template_id' => $id, 'option_id' => $subsec['option_id'],'sortorder' => $subsec['sortorder'],'subtitle' => $subsec['subtitle']] );
							}
						}
					}	
				}

				if( isset($post["template"]["postpricerule"]) ) {
					$postpriceruleModel = Mage::getModel("configurator/postpricerule");
					$postpriceruleModel->setTemplate($templateModel);
					$postpriceruleModel->saveTemplatePostpricerules($post["template"]["postpricerule"]);
				}
				if( isset($post["template"]["rules"]) ) {
					$model = Mage::getModel("configurator/rules");
					$model->setTemplate($templateModel);
					$model->saveTemplateRules($post["template"]["rules"]);
				}

                // Check for loops in the template
                $check_loop = $templateModel->checkForLoops($id);

				if($isAjax){
                    if ($check_loop) {
                        $output['message'] = $templateModel->getLastErrorMessage();
                    }
					$output['message'] = 'success';
					$output['templateId'] = $id;
				}else{
                    if ($check_loop) {
                        Mage::getSingleton("adminhtml/session")->addError($templateModel->getLastErrorMessage());
                    }
					Mage::getSingleton("adminhtml/session")->addSuccess( Mage::helper("configurator")->__("Template was successfully saved") );
					Mage::getSingleton("adminhtml/session")->setConfiguratorData(false);
				}

				// Clean Magento Zend Cache
				$cache = Mage::app()->getCache();
                $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_TEMPLATE_".$id));

			} catch (Exception $e) {
				if($isAjax){
					$output['message'] = 'error: ' .$e->getMessage();
				}else{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setConfiguratorData($this->getRequest()->getPost());
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}

			}
		}

		if($isAjax){
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
		}else{
			if( $id ) {
				$this->_redirect('*/*/edit', array('id' => $id));
			} else {
				$this->_redirect("*/*/");
			}
		}
	}

	public function saveAjaxAction()
	{

        $id = $this->getRequest()->getParam('id',false);
        $optionType = $this->getRequest()->getParam('optiontype',false);
        $saveOption = $this->getRequest()->getParam('so',false);
        $saveOptionValue = $this->getRequest()->getParam('sov',false);
        $option_id = null;
        $templateId = null;
        $output = array();

		if( $this->getRequest()->getPost() && isset($id))
		{
			try {
				$post = $this->getRequest()->getPost();
                // option value save
				if( isset($post["template"]["options"]) ) {
                    if($saveOption){
                        $post["template"]["options"][$id]['sort_order_combiimage'] = $post["template"]["options"][$id]['default_value'];                        
                        $optionModel = Mage::getModel("configurator/option");
                        $templateId =  $post["templateId"];
                        $optionModel = $optionModel->saveTemplateOption($post["template"]["options"][$id], $templateId);
                       	if($optionModel && $optionModel != 'delete'){
							$result = Mage::helper('configurator')->getOptionValueById($optionModel->getId());
							if($result){
								$output['option'] = $result;
							}
                            $option_id = $optionModel->getId();
						}
						Mage::helper('configurator/blacklistCleaning')->clean($templateId);
                    }

                    if($saveOptionValue){
                        $optionModel = Mage::getModel("configurator/option");
                        $templateId =  $post["templateId"];
                        $optionValueValues = $post["template"]["options"][$id]['values'];
                        foreach($optionValueValues as $key=> $optionValueValue){
                        	if(isset($optionValueValue['sku'])){
                        		$product = Mage::getModel('catalog/product');
								$pid = Mage::getModel('catalog/product')->getResource()->getIdBySku($optionValueValue['sku']);
								if ($pid) {
    								$product->load($pid);
	                        		$optionValueValue['product_id'] = $pid;
    	                    		if($optionValueValue['title']==''){    	                    			
        	                			$optionValueValue['title'] = $product->getName();
            	            		}

            	            	}	
                        	}
                            $valueModel = $optionModel->saveTemplateOptionValue($optionValueValue, $id, $templateId);
                        }
                        $output['optionValueId'] = $valueModel->getId();
						$option = Mage::getModel("configurator/option")->load($id);
						if(!$option->getDefaultValue()){
							$output['optionDefaultTitle'] = Mage::helper('catalog')->__('no default');
						}
                    }
				}

                // new option values by csv
                if(isset($post["optionvaluecsv"])){
					session_write_close();

                    $optionModel = Mage::getModel("configurator/option");
					$templateId = $this->getRequest()->getParam('templateId',false);
					$uploadcachekey = $this->getRequest()->getParam('uploadcachekey',false);

					$message = $optionModel->saveOptionValueCsv($post["optionvaluecsv"], $optionType, $id, $templateId, $uploadcachekey);
					$output['matrixstatus'] = $message[0];
					$output['matrixmessage'] = $message[1];
					$output['optionValues'] = $message[2];
				}


                if(isset($post["matrixcsv"])){
                    $optionModel = Mage::getModel("configurator/option");
                    $message = $optionModel->saveMatrixCsv($post["matrixcsv"], $id, $post["delimiter"]);
                    $output['matrixstatus'] = $message[0];
                    $output['matrixmessage'] = $message[1];
                }

				// Clean Magento Zend Cache
                $cache = Mage::app()->getCache();
                if ($templateId) {
                    $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_TEMPLATE_".$templateId));
                }
                if ($id) {
                    $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_OPTION_".$id));
                }

            } catch (Exception $e) {
                $output['message'] = 'error happend at saveAjaxAction: ' .$e->getMessage();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
				return;
			}

            // Check for loops in the template
            if ($templateId) {
                $templateModel = Mage::getModel("configurator/template")->load($templateId);
                $check_loop = $templateModel->checkForLoops($templateId);
            }

            if ($check_loop) {
                $output['message'] = $templateModel->getLastErrorMessage();
            } else {
                $output['message'] = 'success';
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
		}else{
            $output['message'] = 'no valid optionId found';
        }

	}

	public function uploadstatusAction(){
		$output = array();

		$uploadcachekey = $this->getRequest()->getParam('uploadcachekey',false);

		if($uploadcachekey){
			$uploadstatus = Mage::getModel('configurator/uploadstatus')->getByCachekey($uploadcachekey);

			if($uploadstatus->getStatus()){
				$output['status'] = $uploadstatus->getStatus();
				$output['message'] = $uploadstatus->getMessage();
				$output['iterationcount'] = $uploadstatus->getIterationcount() +1;
				$uploadstatus->setIterationcount($output['iterationcount']);
				$uploadstatus->save();
			}
		}

		if(count($output) < 3){
			$output['status'] = 'error';
			$output['message'] = '';
			$output['iterationcount'] = 1;
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id',false);

		if( $id ) {
			/* Delete all relationships of the options */
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->addFieldToFilter("template_id",$id);
			foreach ($options as $option) {
				if ($option->getParentId()) {
					$option->setParentId(NULL);
					$option->save();
				}
			}

			try {
				$templateModel = Mage::getModel("configurator/template")->load($id);
				$templateModel->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess( Mage::helper("configurator")->__("Template was successfully deleted") );
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect("*/*/");
	}

	/**
	 * Duplicates a templated identified by request parameter 'id'.
	 * @author Bodo Schulte
	 */
	public function duplicateAction() {
		$id = $this->getRequest()->getParam('id',false);
		if($id) {
			//$util = new Justselling_Configurator_Model_Utils_TemplateCopy();
                        $util = Mage::getModel('configurator/utils_templatecopy');
			try {
				$util->copy($id);
				Mage::getSingleton("adminhtml/session")
					->addSuccess( Mage::helper("configurator")->__("Template was successfully duplicated") );
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect("*/*/");
	}

	public function logDownloadAction() {
		$logFileName = $this->getRequest()->getParam('l',false);
		$logFileAbs = self::getLogFileNameAbs($logFileName);
		if (file_exists($logFileAbs)) {
			$logFileData = array('type' => 'filename', 'value' => $logFileAbs);
			$this->_prepareDownloadResponse($logFileName, $logFileData);
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('The log file doesn\'t exist.'));
			$this->_redirect("*/*/", array('section'=>'productconfigurator'));
		}
	}

	public static function getLogFileNameAbs($logFileName) {
		$logFileAbs = Mage::getBaseDir().DS.'var'.DS.'log'.DS.$logFileName;
		return $logFileAbs;
	}

    public function exportAction() {
        $id = $this->getRequest()->getParam('id',false);
        if($id) {
            $export = new Justselling_Configurator_Model_Export_Processor();
            try {
                $filename = $export->exportTemplate($id);
                $filepath = Mage::getBaseDir().DS.Justselling_Configurator_Model_Export_Processor::EXPORT_PATH.DS.$filename.".zip";
                $this->_prepareDownloadResponse($filename, array("type"=>"filename", "value"=>$filepath), 'application/zip');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect("*/*/");
    }

    public function importAction() {
        $filename = $this->getRequest()->getParam('filename',false);
		$filename = urldecode($filename);
        if($filename) {
            $export = new Justselling_Configurator_Model_Export_Processor();
            $id = $export->importTemplate($filename);
			if (!$id) {
				Mage::getSingleton('adminhtml/session')
					->addError(
						Mage::helper('configurator')->__("Template import failed for file %s, please check log files.", $filename));
			} else {
				Mage::app()->getHelper('configurator/imageMigration')->migrateAllImages($id);
            	Mage::getSingleton("adminhtml/session")
                    ->addSuccess(
						Mage::helper("configurator")->__("Template was successfully imported with id %s", $id) );
			}
        }
        $this->_redirect("*/*/");
    }

	protected function getDataForm() {
		if (!$this->_form) {
			$form = new Varien_Data_Form(array(
					"id" => "edit_form",
					"action" => $this->getUrl("*/*/save", array(
							"id" => $this->getRequest()->getParam("id")
					)),
					"method" => "post",
					"enctype" => "multipart/form-data"
			));
			$form->setUseContainer(true);
			$this->_form = $form;
		}
		return $this->_form;
	}

	protected function getWysiwygConfig() {
		return Mage::getSingleton('cms/wysiwyg_config')
		->getConfig(array(
				'add_variables' => false,
				'add_widgets' => false,
				'files_browser_window_url'=> Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'admin/cms_wysiwyg_images/index/'
		));
	}

	protected function getCleanId($id) {
		$id =  str_replace("[", "", $id);
		$id =  str_replace("]", "", $id);
		return $id;
	}

	public function optiondetailsAction()
	{
		$_start = microtime(true);

		$params = $this->getRequest()->getParams();

		$html = "";
        $this->loadLayout();
        $block = $this->getLayout()->getBlock("root");

		if( isset($params['id']) ) {

			$optionModel = Mage::getModel('configurator/option')->load($params['id']);

			$onlyDependetOptions = false;
			if(isset($params['optionType'])){
				$optionType = $params['optionType'];
				$onlyDependetOptions = true;
			}else{
				$optionType = $optionModel->getType();
			}

            try {
                $childOptions = $optionModel->getChildrenStatus();
            } catch (Exception $e) {
                $childOptions = array();
            }

			if(!$onlyDependetOptions){
				$html.= "<h4 style='font-size:1.25em;margin-bottom:2px;'>".Mage::helper("configurator")->__("Informations");
                $html .= Mage::helper('configurator')->getHelpIqLink(
                    $block,
                    "helpiq-lightbox",
                    Mage::helper('configurator')->__('optiontext-and-images'),
                    Mage::helper('configurator')->__('Option text and images')
                );
                $html .= "</h4>";
				if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
					$editor = New Varien_Data_Form_Element_Editor();
					$editor->setWysiwyg(true);
					$editor->setConfig($this->getWysiwygConfig());
				} else {
					$editor = New Varien_Data_Form_Element_Textarea();
				}
				$editor->setId($this->getCleanId("template[options][".$params['id']."][add_info]"));
				$editor->setName("template[options][".$params['id']."][add_info]");
				$editor->setLabel(Mage::helper('configurator')->__('Informations'));
				$editor->setTitle(Mage::helper('configurator')->__('Informations'));
				$editor->setForm($this->getDataForm());
				$editor->setValue($optionModel->getInfo());
				$editor->setStyle("height:12em;width:99%");

                $result = $editor->getElementHtml();
                $result = str_replace("varienGlobalEvents.clearEventHandlers", "// varienGlobalEvents.clearEventHandlers", $result);
				$html .= $result;

				$html.= "<br/><br/>";

				$html .= "<h4 style='font-size:1.25em;margin-bottom:2px;'>".Mage::helper("configurator")->__("Add. Informations");
                $html .= Mage::helper('configurator')->getHelpIqLink(
                    $block,
                    "helpiq-lightbox",
                    Mage::helper('configurator')->__('optiontext-and-images'),
                    Mage::helper('configurator')->__('Option text and images')
                );
                $html .= "</h4>";
				if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
					$editor = New Varien_Data_Form_Element_Editor();
					$editor->setWysiwyg(true);
					$editor->setConfig($this->getWysiwygConfig());
				} else {
					$editor = New Varien_Data_Form_Element_Textarea();
				}
				$editor->setId($this->getCleanId("template[options][".$params['id']."][more_info]"));
				$editor->setName("template[options][".$params['id']."][more_info]");
				$editor->setLabel(Mage::helper('configurator')->__('Add. Informations'));
				$editor->setTitle(Mage::helper('configurator')->__('Add. Informations'));
				$editor->setForm($this->getDataForm());
				$editor->setValue($optionModel->getMoreInfo());
				$editor->setStyle("height:12em;width:99%");

                $result = $editor->getElementHtml();
                $result = str_replace("varienGlobalEvents.clearEventHandlers", "// varienGlobalEvents.clearEventHandlers", $result);
                $html .= $result;

				//Js_Log::log('time admin optiondetailsAction create Add. Informations: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}

			if( count($childOptions) > 0) {

				$html.= '<div class="dependentoptions-wrapper">';

				if(in_array($optionType, array('combi','combipricelist', 'selectcombi', 'overlayimagecombi', 'listimagecombi','expression','http', 'matrixvalue'))){
					$html.= '<div class="dependentoptions">';
					$html.= "<h3 style=''>".Mage::helper("configurator")->__("Dependent Option Settings")."</h3>";

					foreach ($childOptions as $childOption) {
						$_start2 = microtime(true);
						$html.= "<div class='dependentoption' >";
						$html.= "<h4>".$childOption['title']." (".$childOption['id'] .")</h4>";

						$html.= "<label>".Mage::helper("configurator")->__("Combination").":</label> ";
						$html.= "<select name='template[options][".$params['id']."][details][".$childOption['id']."][is_combi]'>";
						$html.= "<option value=''>".Mage::helper("configurator")->__("No")."</option>";
						$html.= "<option value='1' ".( (isset($childOption['is_combi']) && $childOption['is_combi'] == 1) ? 'selected="selected"' : '').">".Mage::helper("adminhtml")->__("Yes")."</option>";
						$html.= "</select>";

						$html.= '</div>';

						//Js_Log::log('time admin optiondetailsAction create Dependent Option Settings for id: '.$childOption['id'] .' ' . (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);

					}
					$html.= "<div style='clear:both;'></div>";
					$html.= '</div>';
				}

				$html.= '</div>';
				//Js_Log::log('time admin optiondetailsAction create Dependent Option Settings: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}

		}

		$this->getResponse()->setBody($html);
	}


	public function optionvalueAction()
	{
		$params = $this->getRequest()->getParams();
        $id = $params['id'];

		if(isset($id)){
            $result = Mage::helper('configurator')->getOptionValueById($id);

            if($result){
				$output['option'] = $result;

				$options = Mage::getSingleton("configurator/option")->toOptionArrayWithId($result['template_id']);
				$parentOptionsSelectId = Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Options::getFieldId().'_'.$result['id'] .'_parent';
				$select = $this->getLayout()->createBlock('adminhtml/html_select')
					->setData(array(
						'id' => $parentOptionsSelectId,
						'class' => 'select select-product-option-parent_id required-option-select'
					))
					->setName(Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Options::getFieldName().'['.$result['id'].'][parent_id]')
					->setOptions($options);

				$output['parent_select'] = $select->getHtml();
				$output['parent_selectId'] = $parentOptionsSelectId;

				$output['option'] = $result;

				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
                return true;
            }
		}
        $this->getResponse()->setBody('Error: no option found for id');
	}



	public function optionsAction()
	{
		$params = $this->getRequest()->getParams();
		$output = array();
		$optionArray = array();

		if( isset($params['templateid']) ) {
            $templateId = $params['templateid'];
			$optionArray = Mage::helper('configurator')->getOptionsForTemplateIdAsArray($templateId);
		}

		if(sizeof($optionArray) > 0){
			$output['message'] = 'success';
			$output['options'] = $optionArray;
		}else{
			$output['message'] = 'error';
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
	}
	public function optionvaluesAction()
	{
		$params = $this->getRequest()->getParams();
		$html = "";

		if( isset($params['id']) ) {
            $option_id = $params['id'];
            $option = Mage::getModel("configurator/option")->load($option_id);
			if($params['optionType']){
				$optionType = $params['optionType'];
			}else{
				$optionType = $option->getType();
			}

            switch ($optionType) {
                case "checkbox":
                    $html .= "<option value=\"0\">".Mage::helper('catalog')->__('No')."</option>";
                    $html .= "<option value=\"1\">".Mage::helper('catalog')->__('Yes')."</option>";
                    break;
                default:
                    $html .= "<option value=\"0\">".Mage::helper('catalog')->__('no default')."</option>";
                    $valueModel = Mage::getModel('configurator/value')->getCollection();
                    $valueModel->addFilter("option_id",$option_id);
                    foreach ($valueModel as $value) {
                        $html .= "<option value=\"".$value->getId()."\">".$value->getTitle()."</option>";
                    }
                    break;
            }
		}

		$this->getResponse()->setBody($html);
	}

	public function valuedetailsAction()
	{
		$_start = microtime(true);
		$params = $this->getRequest()->getParams();
		$html = "";
        $this->loadLayout();
        $block = $this->getLayout()->getBlock("root");
		//Js_Log::log('time admin valuedetailsAction $block: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);

		if( isset($params['id']) && isset($params['value']) ) {

			$optionModel = Mage::getModel('configurator/option')->load($params['id']);
			//Js_Log::log('time admin valuedetailsAction $optionModel: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			$childOptions = $optionModel->getChildrenArray();
			//Js_Log::log('time admin valuedetailsAction $childOptions: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			$blacklistData = $optionModel->getBlacklistData($params['value']);
			//Js_Log::log('time admin valuedetailsAction getBlacklistData: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			$optionBlacklistData = $optionModel->getOptionBlacklistData($params['value']);
			//Js_Log::log('time admin valuedetailsAction getOptionBlacklistData: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);


			// Zend_Debug::dump($optionBlacklistData);

			$valueModel = Mage::getModel('configurator/value')->load($params['value']);

            $optionType = $params['optiontype'] ? $params['optiontype'] : $optionModel->getType();

			$fsThumbnail = Mage::getBaseDir('media') .'/configurator/'. $valueModel->getThumbnail();
			$fsImage = Mage::getBaseDir('media') .'/configurator/'. $valueModel->getImage();

			$html.= '<div class="optionvalue-edit">';

			/*
			 * Pricelist Value
			*/
			if( in_array($optionType ,array('selectcombi','overlayimagecombi', 'listimagecombi')) ) {
				$html.= '<div id="template_option_'.$params['id'].'_values_'.$params['value'].'_pricelist" class="pricelistvalues">';
				$html.= "<h3>".Mage::helper("configurator")->__("Pricelist")."</h3>";
				$html.= '</div>';

				$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
				$pricelistValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/pricelist_value');

				$select = $connection->select()->from(array("cpv" => $pricelistValueTable),array("MAX(id)"));
				$lastPriceListId = $connection->fetchOne($select);

				$html.= '<script type="text/javascript">';
				$valuePricelistData = array(
						'option_id' => $params['id'],
						'value_id' => $params['value'],
						'pricelist'=> array()
				);

				if( $optionModel->hasPricelist($params['value']) ) {
					foreach($optionModel->getPricelistData($params['value']) as $pricelistItem) {
						$valuePricelistData['pricelist'][] = array(
								'option_id' => $params['id'],
								'value_id' => $params['value'],
								'id' => $pricelistItem->getId(),
								'value' => $pricelistItem->getValue(),
								'price' => $pricelistItem->getPrice(),
								'operator' => $pricelistItem->getOperator()
						);
					}
				}

				$pricelistValues = Zend_Json_Encoder::encode($valuePricelistData['pricelist']);
				$html.= 'jQuery("#optionTypeValuePriceTable").tmpl('.Zend_Json_Encoder::encode($valuePricelistData).').appendTo("#template_option_'.$params['id'].'_values_'.$params['value'].'_pricelist");';
				$html.= 'jQuery('.$pricelistValues.').each(function(i,item){ jQuery("#template_option_'.$params['id'].'_values_'.$params['value'].'_pricelist_"+item.id+"_operator").val(item.operator); });';
				$html.= '</script>';
				//Js_Log::log('time admin valuedetailsAction configurator_pricelist_value end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}
			/*
			 * Pricelist Value
			*/

			$name = "template[options][".$params['id']."][values][".$params['value']."][details]";
			$html .= '<input type="hidden" name="'.$name.'" value="1"/>';

			/**
			 * Linked Product
			 */
			if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) {
				$html.= "<h4>".Mage::helper("configurator")->__("Linked Product");
                $html .= Mage::helper('configurator')->getHelpIqLink(
                    $block,
                    "helpiq-lightbox",
                    Mage::helper('configurator')->__('erp-integration-/-single-article-in-cart'),
                    Mage::helper('configurator')->__('ERP Integration / Single articles in cart')
                );
                $html .= "</h4>";
				$html.= '<div class="linked-product"><input type="text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][product_id]" value="'.$valueModel->product_id.'" size="10" /></div>';
				//Js_Log::log('time admin valuedetailsAction Linked Product end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}

			/**
			 * Image Settings
			 */
			if( in_array($optionType,array('selectimage', 'overlayimage', 'overlayimagecombi', 'listimage','listimagecombi')) ) {
				$html.= "<h4>".Mage::helper("configurator")->__("Thumbnail Image");
                $html .= Mage::helper('configurator')->getHelpIqLink(
                    $block,
                    "helpiq-lightbox",
                    Mage::helper('configurator')->__('option-values-images'),
                    Mage::helper('configurator')->__('Option values images')
                );
                $html .="</h4>";
                $html .= "</h4>";

				// Thumbnail Settings
				$html.= '<div class="thumbnail-settings">';
				$html.= '<label>'.Mage::helper("configurator")->__("Thumbnail Image Width").'</label><br/>';
				$html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][thumbnail_size_x]" value="'.$valueModel->thumbnail_size_x.'" size="20" />';
				$html.= '</div>';

				$html.= '<div class="thumbnail-settings">';
				$html.= '<label>'.Mage::helper("configurator")->__("Thumbnail Image Height").'</label><br/>';
				$html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][thumbnail_size_y]" value="'.$valueModel->thumbnail_size_y.'" size="20" />';
				$html.= '</div><div class="clearfix" />';



				$html.= '<div class="optionvalueimage file-wrapper" id="file-wrapper-thumbnail-'.$params['value'].'">';
				$html.= '<input type="file" class="input-image" id="thumbnail-'.$params['value'] .'" />';
				$html.= '<div class="image-preview">';
				if( file_exists($fsThumbnail) && $valueModel->getThumbnail() ) {
					$html.= '<img src="'.Mage::getBaseUrl('media') . 'configurator/'. $valueModel->getThumbnail().'" width="100" /><a class="uploadifytag" id="uploadifytag' . '" href="#" ></a>';
                    $fsThumbnailValue = $valueModel->getThumbnail();
				}else{
                    $fsThumbnailValue = '';
                }
                $html.= '</div></div><div class="clearfix" /><input type="hidden" id="hidden-thumbnail-'.$params['value'] .'" class="template-option-image" name="template[options]['.$params['id'].'][values]['.$params['value'].'][thumbnail]" value="'.$fsThumbnailValue .'" />';
				//Js_Log::log('time admin valuedetailsAction option-values-images end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}

            if (true || Mage::getSingleton('core/session')->getEdition()=="P" || Mage::getSingleton('core/session')->getEdition()=="U") {
                if( in_array($optionType,array('selectimage', 'overlayimage', 'overlayimagecombi', 'listimage','listimagecombi','select','selectcombi','radiobuttons')) ) {
                    // Image Settings
                    $html.= "<h4 style='clear:both;'>".Mage::helper("configurator")->__("Image");
                    $html .= Mage::helper('configurator')->getHelpIqLink(
                        $block,
                        "helpiq-lightbox",
                        Mage::helper('configurator')->__('option-values-images'),
                        Mage::helper('configurator')->__('Option values images')
                    );
                    $html .="</h4>";

                    $html.= '<div class="image-settings">';
                    $html.= '<label>'.Mage::helper("configurator")->__("Image Width").'</label><br/>';
                    $html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][image_size_x]" value="'.$valueModel->image_size_x.'" size="20" />';
                    $html.= '</div>';

                    $html.= '<div class="image-settings">';
                    $html.= '<label>'.Mage::helper("configurator")->__("Image Height").'</label><br/>';
                    $html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][image_size_y]" value="'.$valueModel->image_size_y.'" size="20" />';
                    $html.= '</div>';

                    $html.= '<div class="image-settings">';
                    $html.= '<label>'.Mage::helper("configurator")->__("Image Offset X").'</label><br/>';
                    $html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][image_offset_x]" value="'.$valueModel->image_offset_x.'" size="20" />';
                    $html.= '</div>';

                    $html.= '<div class="image-settings">';
                    $html.= '<label>'.Mage::helper("configurator")->__("Image Offset Y").'</label><br/>';
                    $html.= '<input type="text" class="input-text" name="template[options]['.$params['id'].'][values]['.$params['value'].'][image_offset_y]" value="'.$valueModel->image_offset_y.'" size="20" />';
                    $html.= '</div>';

                    $html.= '<div class="clearfix" />';
                    $html.= '<div style="clear:both;margin-top:10px;" class="optionvalueimage file-wrapper" id="file-wrapper-image-'.$params['value'].'">';
                    $html.= '<input type="file" id="image-'.$params['value'].'" class="input-image" value="" />';

                    $html.= '<div class="image-preview">';
                    if( file_exists($fsImage) && $valueModel->getImage() ) {
                        $html.= '<img src="'.Mage::getBaseUrl('media') . 'configurator/'. $valueModel->getImage().'" width="100" /><a class="uploadifytag" id="uploadifytag' . '" href="#" ></a>';
                        $fsImageValue = $valueModel->getImage();
                    }else{
                        $fsImageValue = '';
                    }

                    $html.= '</div></div><div class="clearfix" /><input type="hidden" id="hidden-image-'.$params['value'].'" class="template-option-image" name="template[options]['.$params['id'].'][values]['.$params['value'].'][image]" value="' .$fsImageValue .'" />';
					//Js_Log::log('time admin valuedetailsAction option-values-images enterprice end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
				}
            }

			$html.= '<div>';
			$html.= "<h4 style='font-size:1.25em;margin-bottom:2px;'>".Mage::helper("configurator")->__("Add. Informations")."</h3>";
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$editor = New Varien_Data_Form_Element_Editor();
				$editor->setWysiwyg(true);
				$editor->setConfig($this->getWysiwygConfig());
			} else {
				$editor = New Varien_Data_Form_Element_Textarea();
			}
			$editor->setId($this->getCleanId('template[options]['.$params['id'].'][values]['.$params['value'].'][info]'));
			$editor->setName('template[options]['.$params['id'].'][values]['.$params['value'].'][info]');
			$editor->setLabel(Mage::helper('configurator')->__('Add. Informations'));
			$editor->setTitle(Mage::helper('configurator')->__('Add. Informations'));
			$editor->setForm($this->getDataForm());
			$editor->setValue($valueModel->getInfo());
			$editor->setStyle("height:12em;width:99%");
			$html .= $editor->getElementHtml();
			$html.= '</div>';

			/* Value Tags */
			$name = "template[options][".$params['id']."][values][".$params['value']."][tags]";
			$html.= '<div>';
			$html.= "<h4 style='font-size:1.25em;margin-bottom:2px;'>".Mage::helper("configurator")->__("Tags");
            $html .= Mage::helper('configurator')->getHelpIqLink(
                $block,
                "helpiq-lightbox",
                Mage::helper('configurator')->__('tags'),
                Mage::helper('configurator')->__('Tags')
            );
            $html.="</h4>";
			$html .= Mage::helper('configurator')->__('A list of tags seperated by space.')."<br/>";
			$html .= '<textarea style="width:99%;height:2em;" name="'.$name.'">';
			$html .= $valueModel->getTagsString();
			$html .= '</textarea>';
			$html.= '</div>';

			//Js_Log::log('time admin valuedetailsAction Add. Informations end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);

			/* Option has parent Option */
			if ($optionModel->getParentId()) {
				$parents = $optionModel->getParentArray($optionModel);

				$showme = false;
				foreach ($parents as $parent) {
					$parentmodel = Mage::getModel("configurator/option")->load($parent);
					if ($parentmodel->getValueTagArray()) {
						$showme = true;
					}
				}

				if ($showme) {
					$html.= "<h4 style='font-size:1.25em;margin:10px 0 2px 0;'>".Mage::helper("configurator")->__("Child Options");
                    $html .= Mage::helper('configurator')->getHelpIqLink(
                        $block,
                        "helpiq-lightbox",
                        Mage::helper('configurator')->__('child-options'),
                        Mage::helper('configurator')->__('Child Options')
                    );
					$html .= "</h4>";
					$html.= "<table class='border' cellpadding='0' cellspacing='0'>";
					$html.= "<thead>";
					$html.= "<tr class='headings'>";
					$html.= "<th>".Mage::helper("configurator")->__("Option")."</th>";
					$html.= "<th>".Mage::helper("configurator")->__("Input Type")."</th>";
					$html.= "<th>".Mage::helper("configurator")->__("Blacklist Tags")."</th>";
					$html.= "</tr>";
					$html.= "</thead>";
					$html.= "<tbody>";
					$i= 0;
					foreach ($parents as $parent) {
						$_start2 = microtime(true);
						$parentmodel = Mage::getModel("configurator/option")->load($parent);
						if ($parentmodel->getValueTagArray()) {
							$name = "template[options][".$params['id']."][values][".$params['value']."][optionvaluetagblacklist][related_option_id][".$parent ."][tag][]";
							$html.= "<tr>";
							$html.= "<td>".$parentmodel->getTitle();
							$html.= "<input type='hidden' name='template[options][".$params['id']."][values][".$params['value']."][optionvaluetagblacklist][" .$i ."][related_option_id]' value='".$parent."' />";
							$html.="</td>";
							$html.= "<td>".ucfirst($parentmodel->getType())."</td>";
							$html.= "<td>";
							$html.= "<select multiple='multiple' size='5' style='width:350px;' name=".$name.">";
							$tag_array = $optionModel->getValueTagBlacklistArray($params['id'], $params['value'], $parentmodel->getId());
							foreach($parentmodel->getValueTagArray() as $tag) {
								$selected = "";
								if (in_array($tag, $tag_array)) $selected = 'selected="selected"';
								$html.= "<option value='".$tag."' ".$selected." >".$tag."</option>";
							}
							$html.= "</select>";
							$html.= "<td>";
						}
						$i++;
						//Js_Log::log('time admin valuedetailsAction optionvaluetagblacklist for parent: '.$parent .' ' . (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);
					}
					$html.= "</tbody>";
					$html.= "<table>";
				}
				//Js_Log::log('time admin valuedetailsAction optionvaluetagblacklist end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}


			/* Option is dependend from */
			if( count($childOptions) > 0 ) {
				$i= 0;
				$html.= "<h4 style='font-size:1.25em;margin:10px 0 2px 0;'>".Mage::helper("configurator")->__("Is dependend from");
                $html .= Mage::helper('configurator')->getHelpIqLink(
                    $block,
                    "helpiq-lightbox",
                    Mage::helper('configurator')->__('is-dependend-from'),
                    Mage::helper('configurator')->__('Is dependend from')
                );
                $html .= "</h4>";
				$html.= "<table class='border' cellpadding='0' cellspacing='0'>";
				$html.= "<thead>";
				$html.= "<tr class='headings is-dependendfrom'>";
				$html.= "<th>".Mage::helper("configurator")->__("Option")."</th>";
				$html.= "<th>".Mage::helper("configurator")->__("Blacklist Option")."</th>";
				$html.= "<th>".Mage::helper("configurator")->__("Blacklist Values")."</th>";
				$html.= "</tr>";
				$html.= "</thead>";
				$html.= "<tbody>";
				foreach ($childOptions as $childOption) {
					$_start2 = microtime(true);
					$childOptionValueStatus = $valueModel->getChildOptionValueStatus($childOption['id']);
					$html.= "<tr>";
					$html.= "<td>".$childOption['title'];
					$html.= "<input type='hidden' name='template[options][".$params['id']."][values][".$params['value']."][status][".$i."][id]' value='".$childOptionValueStatus->getId()."' />";
					$html.= "<input type='hidden' name='template[options][".$params['id']."][values][".$params['value']."][status][".$i."][option_id]' value='".$childOption['id']."' />";
					$html.="</td>";

					// Blackist complete option
					$name = "template[options][".$params['id']."][values][".$params['value']."][blacklistoption][".$blacklistData[$childOption['id']]['option_id']."]";
					$html.= "<td><input type='checkbox' name='$name' value='1'";
					if ($blacklistData[$childOption['id']]['selected'] && $blacklistData[$childOption['id']]['selected_option_id'] ==  $params['value']) {
						$html .=  " checked = \"checked\" ";
					}
					$html.= "/></td>";

					//Blackist selected option values
					$html.= "<td>";
					if( isset($blacklistData[$childOption['id']]['values']) && count($blacklistData[$childOption['id']]['values']) > 0) {

						$name = "template[options][".$params['id']."][values][".$params['value']."][blacklist][]";
						$html.= "<select multiple='multiple' size='5' style='width:350px;' name=".$name.">";
						foreach($blacklistData[$childOption['id']]['values'] as $value) {
							$selected = (isset($value['selected']) && $value['selected'] == true) ? 'selected="selected"' : '';
							$html.= "<option value='".$value['id']."' ".$selected." >".$value['title'] ." (" .$value['id'] .")</option>";
						}
						$html.= "</select>";
					}

					if (isset($optionBlacklistData[$childOption['title']])) {
						// Option-Id, hidden
						$name = "template[options][".$params['id']."][values][".$params['value']."][optionblacklist][optionid][]";
						$html .= '<input id="'.$name.'" name="'.$name.'" type="hidden" value="'.$optionBlacklistData[$childOption['title']]['option_id'].'" />';

						// Blacklist-Id, hidden
						$name = "template[options][".$params['id']."][values][".$params['value']."][optionblacklist][blacklistid][]";
						$html .= '<input id="'.$name.'" name="'.$name.'" type="hidden" value="'.$optionBlacklistData[$childOption['title']]['blacklist_id'].'" />';

						// Operator Select Box
						$name = "template[options][".$params['id']."][values][".$params['value']."][optionblacklist][operator][]";
						$ops = array("&lt;" => "<","&lt;=" => "<=","&gt;" => ">","&gt;=" => ">=","=" => "=","!=" => "!=");
						$html .= '<select id="'.$name.'" name="'.$name.'"><option value="">'.Mage::helper("configurator")->__("-- no dependency --").'</option>';
						foreach ($ops as $key => $value) {
							$option = "<option";
							if ($optionBlacklistData[$childOption['title']]["operator"] == $value)
								$option.= ' selected="selected"';
							$option .= ">".$key;
							$option .= "</option>";
							$html .= $option;
						}
						$html .= "</select>";

						// Value, Input text
						$name = "template[options][".$params['id']."][values][".$params['value']."][optionblacklist][value][]";
						$html .= '<input id="'.$name.'" name="'.$name.'" type="text" value="'.$optionBlacklistData[$childOption['title']]["value"].'"/>';
					}
					$html.= "</td>";
					$html.= '</tr>';

					$i++;
					//Js_Log::log('time admin valuedetailsAction blacklistoption for id ' .$childOptionValueStatus->getId() .' :' . (microtime(true) - $_start2), "profile", Zend_Log::DEBUG, true);
				}
				$html.= '</tbody>';
				$html.= '</table>';
				//Js_Log::log('time admin valuedetailsAction blacklistoption end: ' . (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
			}

			$html.= "</table><br/><br/>";
		}
		$this->getResponse()->setBody($html);
	}

	public function clearcacheAction() {
		$templateModel = Mage::getModel("configurator/template");
		$templateModel->clearProductCache();

		$this->getResponse()->setBody("Clear Cache done!");
	}

	public function savefontAction() {
		$params = $this->getRequest()->getParams();

		if( isset($params['fonts']) && isset($params['optionid']) ) {
			$collection = Mage::getModel('configurator/optionfont')->getOptionFonts($params['optionid']);
			foreach($collection as $optionfont) {
				$optionfont->delete();
			}

			$fonts = explode(",", $params['fonts']);
			foreach ($fonts as $fontoption_id) {
				$font_option = Mage::getModel("configurator/optionfont");
				$font_option->setOptionId($params['optionid']);
				$font_option->setFontId($fontoption_id);
				$font_option->save();
			}
		}

		if( isset($params['colors']) && isset($params['optionid']) ) {
			$collection = Mage::getModel('configurator/optionfontcolor')->getOptionFontColors($params['optionid']);
			foreach($collection as $optionfont) {
				$optionfont->delete();
			}

			$fonts = explode(",", $params['colors']);
			foreach ($fonts as $fontcolor) {
				$parts = explode(" ",$fontcolor);
				$code = $parts[0];
				$title = $parts[1];
				$fontcolor = Mage::getModel("configurator/optionfontcolor");
				$fontcolor->setOptionId($params['optionid']);
				$fontcolor->setColorCode($code);
				$fontcolor->setColorTitle($title);
				$fontcolor->save();
			}
		}

		if( isset($params['pos']) && isset($params['optionid']) ) {
			$collection = Mage::getModel("configurator/optionfontposition")->getOptionFontPositions($params['optionid']);
			foreach($collection as $optionfontpos) {
				$optionfontpos->delete();
			}

			$fontposs = explode(",", $params['pos']);
			foreach ($fontposs as $fontpos) {
				$parts = explode(" ",$fontpos);
				$poss = explode("-",$parts[0]);
				$posx = $poss[0];
				$posy = $poss[1];
				$title = $parts[1];
				if (count($parts) > 1) {
					for ($i=2; $i<count($parts); $i++) {
						$title .= " ".$parts[$i];
					}
				}
				$fontposition = Mage::getModel("configurator/optionfontposition");
				$fontposition->setOptionId($params['optionid']);
				$fontposition->setPosX($posx);
				$fontposition->setPosY($posy);
				$fontposition->setPosTitle($title);
				$fontposition->save();
			}
		}

		if( isset($params['optionid']) && isset($params['min_font_size']) && isset($params['max_font_size']) && isset($params['min_font_angle']) && isset($params['max_font_angle']) ) {
			$font_conf = Mage::getModel("configurator/optionfontconfiguration")->getOptionFontConfiguration($params['optionid'])->getFirstItem();
			$font_conf->setOptionId($params['optionid']);
			$font_conf->setMinFontSize($params['min_font_size']);
			$font_conf->setMaxFontSize($params['max_font_size']);
			$font_conf->setMinFontAngle($params['min_font_angle']);
			$font_conf->setMaxFontAngle($params['max_font_angle']);
			$font_conf->setChooseFont($params['choose_font']);
			$font_conf->setChooseFontSize($params['choose_font_size']);
			$font_conf->setChooseFontAngle($params['choose_font_angle']);
			$font_conf->setChooseFontColor($params['choose_font_color']);
			$font_conf->setChooseFontPos($params['choose_font_pos']);
			$font_conf->setChooseTextAlignment($params['choose_text_alignment']);

			$font_conf->save();
		}

		$this->getResponse()->setBody("done");
	}

	public function getfontconfigurationAction() {
		$params = $this->getRequest()->getParams();
		$result = array();

		if( isset($params['optionid']) ) {
			$font_conf = Mage::getModel("configurator/optionfontconfiguration")->getOptionFontConfiguration($params['optionid'])->getFirstItem();
			$result['min_font_size'] = $font_conf->getMinFontSize();
			$result['max_font_size'] = $font_conf->getMaxFontSize();
			$result['min_font_angle'] = $font_conf->getMinFontAngle();
			$result['max_font_angle'] = $font_conf->getMaxFontAngle();
			$result['choose_font'] = $font_conf->getChooseFont();
			$result['choose_font_size'] = $font_conf->getChooseFontSize();
			$result['choose_font_angle'] = $font_conf->getChooseFontAngle();
			$result['choose_font_color'] = $font_conf->getChooseFontColor();
			$result['choose_font_pos'] = $font_conf->getChooseFontPos();
			$result['choose_text_alignment'] = $font_conf->getChooseTextAlignment();

			$fonts_result = array();
			$fonts = Mage::getModel('configurator/optionfont')->getOptionFonts($params['optionid']);
			foreach ($fonts as $font) {
				$font_details = Mage::getModel('configurator/font')->load($font->getFontId());
				$fonts_result[$font->getFontId()] = $font_details->getTitle()." ".$font_details->getTypeString($font_details->getFontType());
			}
			$result['fonts'] = $fonts_result;

			$fonts_result = array();
			$fonts = Mage::getModel('configurator/optionfontcolor')->getOptionFontColors($params['optionid']);
			foreach ($fonts as $font) {
				$fonts_result[$font->getColorCode()] = $font->getColorTitle();
			}
			$result['colors'] = $fonts_result;

			$fonts_result = array();
			$fontposs = Mage::getModel('configurator/optionfontposition')->getOptionFontPositions($params['optionid']);
			foreach ($fontposs as $fontpos) {
				$fonts_result[$fontpos->getPosX()."-".$fontpos->getPosY()] = $fontpos->getPosTitle();
			}
			$result['pos'] = $fonts_result;

			$this->getResponse()->setBody(json_encode($result));
			return true;
		}

		$this->getResponse()->setBody("no option-id");
	}

	public function matrixcsvAction() {
		$params = $this->getRequest()->getParams();

		if( isset($params['id']) ) {
			$optionid = $params['id'];
			$option = Mage::getModel("configurator/option")->load($optionid);

			if ($option->getId()) {
				$matrixmodell = Mage::getModel("configurator/optionmatrix")->loadByOptionId($option->getId());
				$matrix = json_decode($matrixmodell->getMatrix());

				$csv = array();
				$csv[0] = array(" ");
				$delimiter = ",";
				if ($option->getMatrixCsvDelimiter())	 $delimiter = $option->getMatrixCsvDelimiter();

				$x = 0;
				if ($matrix) {
					foreach ($matrix as $key => $values) {
						$csv[0][] = $key;
						$y = 0;
						foreach ($values as $ykey => $value) {
							$csv[$y+1][0] = $ykey;
							$csv[$y+1][$x+1] = $value;
							$y++;
						}
						$x++;
					}

					$content = "";
					foreach ($csv as $csvline) {
						$line = "";
						foreach ($csvline as $column) {
							if (strlen($line)) $line .= $delimiter;
							$line .= $column;
						}
						$content .= $line ."\n";
					}

					$filename = "export.csv";
					$this->_prepareDownloadResponse($filename, $content, "text/csv");
				} else {
					$session = $this->_getSession();
					$session->addError($this->__('No CSV was uploaded yet!'));
					$this->_redirect('*/*/edit/id/'.$option->getTemplateId());
				}
			}
		}
	}

	public function copyoptionAction() {
		$params = $this->getRequest()->getParams();


		if( isset($params['id']) ) {
			$optionid = $params['id'];
			$option = Mage::getModel("configurator/option")->load($optionid);

			$output = array();
			if ($option->getId()) {
				try {
					$newOption = Mage::getModel("configurator/option");
					$option->unsetData('id');
					$newOption->addData($option->getData());
					$newOption->setAltTitle($newOption->getAltTitle() .'_1');
					$newOption->save();

					$optionValues = Mage::getModel("configurator/value")->getCollection()
					->addFieldToFilter('option_id', $optionid);
					foreach ($optionValues as $optionValue){
						$isDefaultValueOption = false;
						if (isset($newOption['default_value']) && $optionValue['id'] == $newOption['default_value']) {
							$isDefaultValueOption = true;
						}
						$optionValue->unsetData('id');
						$newOptionValue = Mage::getModel("configurator/value");
						$newOptionValue->addData($optionValue->getData());
						$newOptionValue->setOptionId($newOption->getId());
						$newOptionValue->save();
						// set right default value
						if($isDefaultValueOption){
							$newOption->setDefaultValue($newOptionValue['id']);
							$newOption->save();
						}
					}
					$result = Mage::helper('configurator')->getOptionValueById($newOption->getId());
					if($result){
						$output['option'] = $result;
					}
					$output['message'] = 'success';
				}catch (Exception $e){
					$output['message'] = $this->__('Failed to copy option');
				}
			}else{
				$output['message'] = $this->__('No option for optionId ' .$optionid .' found');
			}
		}else{
			$output['message'] = $this->__('No optionId found');
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));

	}

	protected function iterateVector($vector, &$result, $opt_vector, &$result_iterator) {
		foreach ($vector[$opt_vector]['values'] as $value) {
			$value_id = $value['id'];
			$result[$result_iterator][$vector[$opt_vector]['id']] = $value_id;

			// Fill in missing and duplicate values
			if ($opt_vector > 0 && $result_iterator > 0) {
				for ($i = $opt_vector-1; $i >= 0; $i--) {
					if (! isset($result[$result_iterator][$vector[$i]['id']])) {
						$result[$result_iterator][$vector[$i]['id']] = $result[$result_iterator-1][$vector[$i]['id']];
					}
				}
			}

			// Check if there are other options available
			if ($opt_vector < sizeof($vector)-1) {
				$this->iterateVector($vector, $result, $opt_vector+1, $result_iterator);
			}
			$result_iterator++;
		}
		$result_iterator--;
	}

	public function generatesingleproductsAction() {
		$params = $this->getRequest()->getParams();

		if( isset($params['id']) ) {
			$templateId = $params['id'];
			$base_product_id = $params['base_product_id'];
			$stock_amount = $params['stock_amount'];
			$config_param = $params['config'];
			$items = explode(",", $config_param);

			/* Get all the given options from the config string */
			$config = array();  // config with value-title
			$vector = array();  // array to iterate all permutations of the option values
			$current_option = null;
			foreach ($items as $item) {
				if (preg_match("/option-(.*)/", $item, $matches)) {
					$config[$matches[1]] = array();
					$current_option = $matches[1];
					$vector[]['id'] = $current_option; $opt_vector = sizeof($vector)-1;
				}
				if (preg_match("/value-(.*)/", $item, $matches)) {
					$value = Mage::getModel("configurator/value")->load($matches[1]);
					if ($current_option && $value->getId())
						$config[$current_option][] = $value->getTitle();
					$vector[$opt_vector]['values'][]['id'] = $value->getId(); $value_vector = sizeof($vector[$opt_vector]['values'])-1;
					$vector[$opt_vector]['values'][$value_vector]['title'] = $value->getTitle();
				}
			}

			/* Generate the attributes and attribute set for the products */
			$singleproduct = Mage::getModel("configurator/singleproduct");
			$setId = $singleproduct->createAttributeSet($templateId);
			if ($setId) {
				$groupId = $singleproduct->addGroupToAttributeSet($setId);
				foreach ($config as $optionId => $values) {
					$option = Mage::getModel("configurator/option")->load($optionId);
					$name = substr("prodconf_".$templateId."_".$option->getAltTitle(), 0, 30);
					$attribId = $singleproduct->addAttributeToAttributeSet($setId, $groupId, $name, $option->getTitle(), "int", "select", $option->getSortOrder(), $values, Justselling_Configurator_Model_Singleproduct::ATTRIBUTE_FILTER);
				}

				/* Add Attribute for deeplin-url */
				$attribId = $singleproduct->addAttributeToAttributeSet($setId, $groupId, "prodconf_deeplink", "Configurator Deeplink", "varchar", "text", 99999, NULL, Justselling_Configurator_Model_Singleproduct::ATTRIBUTE_NOFILTER);
			}

			/* Start Job Master Action here for generating products */
			$result = array();
			$result_iterator = 0;
			$this->iterateVector($vector, $result, 0, $result_iterator);

			/* generate job-id */
			$job_id = 1;
			$lastRecordCollection = Mage::getModel('configurator/singleproduct_job')
			->getCollection()
			->setOrder('job_id', 'desc');
			$lastRecord = $lastRecordCollection->getFirstItem();
			if ($lastRecord->getJobId())
				$job_id = $lastRecord->getJobId()+1;

			/* initiate job-processor */
			try {
				$params = array ("job_id" => $job_id, "template_id" => $templateId, "attribute_set_id" => $setId, "base_product_id" => $base_product_id, "stock_amount" => $stock_amount, "products" => $result);
				$job = Justselling_Configurator_Model_Jobprocessor_Job::createInstance();
				$job->setParams($params);
				$job->setName($this->__("Single Products"));
				$job->setModel('configurator/singleproduct');
				$job->save();

				$this->getResponse()->setBody("Success");
			} catch ( Exception $e ) {
				$this->getResponse()->setBody("Problem: ".$e->getMessage ());
			}
		}
	}

	public function loadgearproductAction(){
		$sku =  $this->getRequest()->getParam('sku');
		$data = array();
		if($sku){
    		$product = Mage::getModel('catalog/product');
			$pid = Mage::getModel('catalog/product')->getResource()->getIdBySku($sku);
			if ($pid) {
				$product->load($pid);
        		$data['product_id'] = $pid;
        		$data['title'] = $product->getName();
        	}	
        	else{ $data['error'] = 1;}
    	}
    	echo json_encode($data);
	}
}