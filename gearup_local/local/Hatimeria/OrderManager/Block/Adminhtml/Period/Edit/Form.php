<?php
/**
 * Order Add Form
 */
class Hatimeria_OrderManager_Block_Adminhtml_Period_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    const ORDER_IS_HIDDEN = 1;

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('edit_form');
        $this->setTitle(Mage::helper('hordermanager')->__('Period'));
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('hordermanager');
        $periodModel = Mage::registry('period');
        $orderModel = Mage::registry('order');

        $ordersCollection = Mage::getModel('hordermanager/order')->getCollection()
            ->addFieldToFilter('period_id', $periodModel->getId())
            ->addFieldToFilter('is_hidden', self::ORDER_IS_HIDDEN);

        $ordersIds = array();
        foreach ($ordersCollection as $order) {
            $ordersIds[$order->getOrderId()] = $order->getOrderId();
        }

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('base_fieldset', array(
            'name'      =>'desc',
            'legend'    => $helper->__('Restore Order'),
            'class'     => 'fieldset-wide'
        ));

        $form->setHtmlIdPrefix('period_');

        if ($periodModel->getId()) {
            $fieldset->addField('period_id', 'hidden', array(
                'name'  => 'period_id',
                'value' => $periodModel->getId()
            ));
        }

        $fieldset->addField('select_order', 'select', array(
            'label'     => $helper->__('Select Order'),
            'name'      => 'order_id',
            'options'   => $ordersIds
        ));

        $fieldset->addField('select_visibility', 'select', array(
            'label'     => $helper->__('Select Visibility'),
            'name'      => 'visibility',
            'options'   =>array(
                '0' => $helper->__('Visible'),
                '1' => $helper->__('Hidden')
            )
        ));


        if (!$orderModel->getId()) {
            $orderModel->setData('active', '1');
        }

        $orderData = $orderModel->getData();
        $periodData = $periodModel->getData();

        $data = array_merge($orderData, $periodData);
        unset($data['date_from']);
        unset($data['date_to']);

        $form->setValues($data);

        return parent::_prepareForm();
    }
}
