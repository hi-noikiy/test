<?php


class Collinsharper_Beanstreaminterac_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('beanstreaminterac/standard/form.phtml');
        parent::_construct();
    }
}