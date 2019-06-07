<?php
/**
 * Order Add Form
 */
class Hatimeria_OrderManager_Block_Adminhtml_Order_New_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle(Mage::helper('hordermanager')->__('New Order'));
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('hordermanager');
        $orderModel = Mage::registry('current_order');

        $salesOrdersCollection = Mage::getModel('sales/order')->getCollection();
        $salesOrdersCollection->setOrder('entity_id');
        $salesOrdersCollection->setPageSize(200);

        $salesOrdersIds = array();
        foreach ($salesOrdersCollection as $order) {
            $salesOrdersIds[$order->getId()] = $order->getIncrementId();
        }

        $periodsCollection = Mage::getModel('hordermanager/period')->getCollection();
        $periodsCollection->setOrder('period_id');
        $periodsCollection->setPageSize(50);

        $periodsIds = array();
        foreach ($periodsCollection as $period) {
            $periodsIds[$period->getPeriodId()] = $period->getCustomPeriodId();
        }

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('base_fieldset', array(
            'name'      => 'desc',
            'legend'    => $helper->__('Restore Order'),
            'class'     => 'fieldset-wide'
        ));

        $form->setHtmlIdPrefix('period_');

        $fieldset->addField('select_order', 'select', array(
            'label'     => Mage::helper('hordermanager')->__('Select Order Id'),
            'name'      => 'select_order',
            'values' => $salesOrdersIds,
        ));

        $fieldset->addField('select_period', 'select', array(
            'label'     => Mage::helper('hordermanager')->__('Select Period Id'),
            'name'      => 'select_period',
            'values' => $periodsIds,
        ));

        $orderData = $orderModel->setData($this->getData('select_order'));
        $periodData = $orderModel->setData($this->getData('select_period'));

        $data = array(
            $orderData,
            $periodData
        );

        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
