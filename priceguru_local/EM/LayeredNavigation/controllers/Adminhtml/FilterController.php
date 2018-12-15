<?php
class EM_LayeredNavigation_Adminhtml_FilterController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu('emthemes/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Layered Navigation Filters'), Mage::helper('adminhtml')->__('Layered Navigation Filters'));
		return $this;
	}
	
	public function indexAction() {
		$this->_initAction();
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('layerednavigation')->__('Layered Navigation Filters'));
		$this->renderLayout();
	}
	
	public function editAction() {
		try {
			// Get ID and create model
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('layerednavigation/filter')->load($id);
			if (!$model->getId()) throw new Exception('This filter no longer exists.');

			// Initial checking
			$this->_title($model->getTitle());

			// Set entered data if was error when we do save
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			// Register model to use later in blocks
			Mage::register('layerednavigation_filter', $model);
			
			// Build edit form
			$this->_initAction()
				->_addBreadcrumb(
					Mage::helper('layerednavigation')->__('Edit Filter'), 
					Mage::helper('layerednavigation')->__('Edit Filter')
			)->renderLayout();
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('layerednavigation')->__($e->getMessage()));
			$this->_redirect('*/*/');
		}
	}
	
	public function saveAction() {
		// check if data sent
		if ($data = $this->getRequest()->getPost()) {
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('layerednavigation/filter')->load($id);
			if (!$model->getId() && $id) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('layerednavigation')->__('This filter no longer exists.'));
				$this->_redirect('*/*/');
				return;
			}
			
			// init model and set data
			$model->setData($data);
			
			// try to save it
			try {
				// save image for option
				$options = $this->getRequest()->getParam('options');
				if (is_array($options)) {
					foreach($options as $op) {
						$this->_saveOption($op);
					}
				}

				// save the data
				$model->save();
				
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('layerednavigation')->__('The filter has been saved.'));
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array(
						'id' => $model->getId()
					));
					return;
				}
				// go to grid
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// save data in session
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				// redirect to edit form
				$this->_redirect('*/*/edit', array(
					'id' => $this->getRequest()->getParam('id')
				));
				return;
			}
		}
		$this->_redirect('*/*/');
	}
	
	protected function _saveOption($data) {
		// get option id, option value
		$oData = explode('_', $data, 2);
		$oId = $oData[0];
		$oVal = $oData[1];

		$opt = Mage::getModel('layerednavigation/option');
		$opt->load($oId);
		if ($opt->getId()) {
			if ($oVal != $opt->getImage())	// delete old image
				unlink(Mage::helper('layerednavigation')->getImagePath($opt->getImage()));
			$opt->setImage($oVal);
			$opt->save();
		}
	}
}