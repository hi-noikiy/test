<?php

class Gearup_Shippingffdx_Adminhtml_Shippingffdx_DestinationController extends Mage_Adminhtml_Controller_action
{
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu("gearup_shippingffdx/destination")
			->_addBreadcrumb(Mage::helper("adminhtml")->__("Items Manager"), Mage::helper("adminhtml")->__("Item Manager"));

		return $this;
	}

	public function indexAction() {
		$this->_initAction();
        $this->_title(Mage::helper('gearup_sds')->__('Gearup'))->_title(Mage::helper('gearup_sds')->__('Destination'));
		$this->renderLayout();
	}

    public function editAction() {

		$id     = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("gearup_shippingffdx/destination")->load($id);

		if ($model->getDestinationId() || $id == 0) {
			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register("destination_data", $model);

			$this->loadLayout();
			$this->_setActiveMenu("gearup_shippingffdx/destination");

			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Item Manager"), Mage::helper("adminhtml")->__("Item Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Item News"), Mage::helper("adminhtml")->__("Item News"));

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			if (Mage::getSingleton("cms/wysiwyg_config")->isEnabled()) {
            	$this->getLayout()->getBlock("head")->setCanLoadTinyMce(true);
            }

			$this->_addContent($this->getLayout()->createBlock("gearup_shippingffdx/adminhtml_destination_edit"));

			$this->renderLayout();
		} else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("gearup_sds")->__("Item does not exist"));
			$this->_redirect("*/*/");
		}
	}

	public function newAction() {
		$this->_forward("edit");
	}

    public function saveAction() {
		if ($data = $this->getRequest()->getPost('destination')) {
            $data['destination'] = Mage::getModel('directory/country')->loadByCode($data['code'])->getName();

			$model = Mage::getModel("gearup_shippingffdx/destination");
			$model->setData($data)->setId($this->getRequest()->getParam("id"));

			try {
				$model->save();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("gearup_sds")->__("Destination number was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setFormData(false);

				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setFormData($data);
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }
        }
        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("gearup_sds")->__("Unable to find destination number to save"));
        $this->_redirect("*/*/");
	}

    public function deleteAction() {
		if ( $this->getRequest()->getParam("id") > 0 ) {
			try {
				$model = Mage::getModel("gearup_shippingffdx/destination");
				$model->setId($this->getRequest()->getParam("id"))
					->delete();

				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Destiantion number was successfully deleted"));
				$this->_redirect("*/*/");
			} catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			}
		}
		$this->_redirect("*/*/");
	}
}