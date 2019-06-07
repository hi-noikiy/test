<?php

abstract class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Abstract
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _getLabelModel()
    {
        $model = Mage::registry('prolabels_rules')
            ? Mage::registry('prolabels_rules')
            : Mage::registry('prolabels_system_rules');
        if ($model->getId()) {
            $rulesId = $model->getRulesId();
        } else {
            $rulesId = $this->getRequest()->getParam('rulesid');
            if ($rulesId) {
                $model->addData(array('rules_id' => $rulesId));
            }
        }

        return $model;
    }

    public function getForm()
    {
        if (!isset($this->_form)) {
            $this->_form = new Varien_Data_Form();
            $this->_form->setHtmlIdPrefix('rules_');
        }

        return $this->_form;
    }
}
