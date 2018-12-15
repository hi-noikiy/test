<?php
class HN_Salesforce_Adminhtml_Salesforce_RetrieveController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$model = Mage::getSingleton('salesforce/field');
		$table = $model->getAllTable();
		foreach ($table as $s_table => $m_table) {
			$model = Mage::getModel('salesforce/field');
			$model->saveFields($s_table, $m_table, true);
		}
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Update Fields success !'));
		$this->_redirect('*/salesforce_map/index');
	}

	public function loadAction() {

		$params = $this->getRequest()->getParams('type');
		$type = $params['type'];
		if(!$type){
			$out['magento_options'] = "";
			$out['salesforce_options'] = "";

			echo json_encode($out);
			return;
		}
		$model = Mage::getSingleton('salesforce/field');
		$salesFields = $model->getSalesforceFields($type);
		$table = $model->getAllTable();
		$mageFields = $model->getMagentoFields($table[$type]);

		$magentoOption = '';
        foreach ($mageFields as $value => $label) {
            $magentoOption .= "<option value='$value' >" . $label . "</option>";
        }
        $out['magento_options'] = $magentoOption;

        $salesforceOption = '';
        foreach ($salesFields as $value => $label) {
            $salesforceOption .=  "<option value='$value' >" . $label . "</option>";
        }
        $out['salesforce_options'] = $salesforceOption;

        echo json_encode($out);
        return;
	}

	protected function _isAllowed()
	{
	    return Mage::getSingleton('admin/session')->isAllowed('salesforce/map');  
	}
}
