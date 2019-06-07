<?php
/**
 * Mish extension BankAccount adjustment
 */

class Mish_BankPayment_Block_Adminhtml_BankAccount extends Phoenix_BankPayment_Block_Adminhtml_BankAccount
{
    protected function _getRowTemplateHtml($i=0)
    {
        $html = '<fieldset><li>';
        $html .= '<label>'.$this->__('Beneficiary name').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[account_holder][]" value="' . $this->_getValue('account_holder/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li><label>'.$this->__('Beneficiary address').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[account_holder_address][]" value="' . $this->_getValue('account_holder_address/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li><label>'.$this->__('Account number').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[account_number][]" value="' . $this->_getValue('account_number/'.$i) . '" '.$this->_getDisabled().' /> </li> ';
        $html .= '<li><label>'.$this->__('Sort code').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[sort_code][]" value="' . $this->_getValue('sort_code/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li> <label>'.$this->__('Beneficiary Bank name').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[bank_name][]" value="' . $this->_getValue('bank_name/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li> <label>'.$this->__('Beneficiary Bank address').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[bank_address][]" value="' . $this->_getValue('bank_address/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li> <label>'.$this->__('Beneficiary IBAN number').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[iban][]" value="' . $this->_getValue('iban/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li> <label>'.$this->__('Beneficiary SWIFT code').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[swift][]" value="' . $this->_getValue('swift/'.$i) . '" '.$this->_getDisabled().' /> </li>';
        $html .= '<li> <label>'.$this->__('BIC').':</label>';
        $html .= '<input class="input-text" type="text" name="'.$this->getElement()->getName().'[bic][]" value="' . $this->_getValue('bic/'.$i) . '" '.$this->_getDisabled().' /> </li> ';
        $html .= '<li>';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</li></fieldset>';

        return $html;
    }
}