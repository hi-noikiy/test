<?php
class EM_Apiios_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_store = null;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    public function getNumberFromPrice($stringPrice){
        preg_match('/<span.*?>(.*?)<\/span>/ism', $stringPrice,$tmp);
        return $tmp[1];
    }

    public function showPrice(Mage_Catalog_Model_Product $_product,$displayMinimalPrice = false){
        if($_product->getTypeId() != 'bundle'){/* Not bundle product */
            $_coreHelper = Mage::helper('core');
            $_weeeHelper = Mage::helper('weee');
            $_taxHelper  = Mage::helper('tax');
            /* @var $_coreHelper Mage_Core_Helper_Data */
            /* @var $_weeeHelper Mage_Weee_Helper_Data */
            /* @var $_taxHelper Mage_Tax_Helper_Data */

            $_storeId = $_product->getStoreId();
            $_id = $_product->getId();
            $_weeeSeparator = '';
            $_weeeStringInc = '';
            $_weeeStringExc = '';
            $_weeeStringReg = '';
            $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
            $_minimalPriceValue = $_product->getMinimalPrice();
            $_minimalPrice = $_taxHelper->getPrice($_product, $_minimalPriceValue, $_simplePricesTax);
            $incTax = array();
            $excludeTax = array();
            $regular = array();
            $minimal = array();

            if (!$_product->isGrouped()):
                    $_weeeTaxAmount = $_weeeHelper->getAmountForDisplay($_product);
                    if ($_weeeHelper->typeOfDisplay($_product, array(Mage_Weee_Model_Tax::DISPLAY_INCL_DESCR, Mage_Weee_Model_Tax::DISPLAY_EXCL_DESCR_INCL, 4))):
                            $_weeeTaxAmount = $_weeeHelper->getAmount($_product);
                            $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForDisplay($_product);
                    endif;
                    $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
                    if ($_weeeHelper->isTaxable() && !$_taxHelper->priceIncludesTax($_storeId)):
                            $_attributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
                            $_weeeTaxAmountInclTaxes = $_weeeHelper->getAmountInclTaxes($_attributes);
                    endif;

                    $_price = $_taxHelper->getPrice($_product, $_product->getPrice());
                    $_regularPrice = $_taxHelper->getPrice($_product, $_product->getPrice(), $_simplePricesTax);
                    $_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
                    $_finalPriceInclTax = $_taxHelper->getPrice($_product, $_product->getFinalPrice(), true);
                    $_weeeDisplayType = $_weeeHelper->getPriceDisplayType();
                    if ($_finalPrice >= $_price):
                            if ($_taxHelper->displayBothPrices()):
                                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including
                                            $excludeTax['label'] =  Mage::helper('tax')->__('Excl. Tax:');
                                            $excludeTax['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $excludeTax['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                            $incTax['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $incTax['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $incTax['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);

                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee
                                            $excludeTax['label'] =  Mage::helper('tax')->__('Excl. Tax:');
                                            $excludeTax['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $excludeTax['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                            $incTax['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $incTax['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $incTax['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $_weeeStringInc .= $_weeeSeparator;
                                                    $_weeeStringInc .= $_weeeTaxAttribute->getName().' : '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $incTax['weee'] = '('.$_weeeStringInc.')';
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee
                                            $excludeTax['label'] =  Mage::helper('tax')->__('Excl. Tax:');
                                            $excludeTax['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $excludeTax['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                            $incTax['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $incTax['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $incTax['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $_weeeStringInc .= $_weeeSeparator;
                                                    $_weeeStringInc .= $_weeeTaxAttribute->getName().' : '.$_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $incTax['weee'] = '('.$_weeeStringInc.')';
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final
                                            $excludeTax['label'] =  Mage::helper('tax')->__('Excl. Tax:');
                                            $excludeTax['value'] = $_coreHelper->currency($_price, true, false);
                                            $excludeTax['value_org'] = $_coreHelper->currency($_price, false, false);
                                            $incTax['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $incTax['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $incTax['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $excludeTax['weee'][] = $_weeeStringExc .= $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                            endforeach;
                                    else:
                                            $excludeTax['label'] =  Mage::helper('tax')->__('Excl. Tax:');
                                            $incTax['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            if ($_finalPrice == $_price):
                                                $excludeTax['value'] = $_coreHelper->currency($_price, true, false);
                                                $excludeTax['value_org'] = $_coreHelper->currency($_price, false, false);
                                            else:
                                                $excludeTax['value'] = $_coreHelper->currency($_finalPrice, true, false);
                                                $excludeTax['value_org'] = $_coreHelper->currency($_finalPrice, false, false);
                                            endif;
                                            $incTax['value'] = $_coreHelper->currency($_finalPriceInclTax, true, false);
                                            $incTax['value_org'] = $_coreHelper->currency($_finalPriceInclTax, false, false);
                                    endif;
                            else:
                                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including
                                            $regular['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $regular['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee
                                            $regular['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $regular['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $_weeeStringReg .= $_weeeSeparator;
                                                    $_weeeStringReg .= $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $regular['weee'] = '('.$_weeeStringReg.')';
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee
                                            $regular['value'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                            $regular['value_org'] = $_coreHelper->currency($_price + $_weeeTaxAmount, false, false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $_weeeStringReg .= $_weeeSeparator;
                                                    $_weeeStringReg .= $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $regular['weee'] = '('.$_weeeStringReg.')';
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final
                                            $regular['value'] = $_coreHelper->currency($_price,true,false);
                                            $regular['value_org'] = $_coreHelper->currency($_price,false,false);
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $regular['weee'][] = $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                            endforeach;
                                                    $regular['price_weeTaxAmount'] = $_coreHelper->currency($_price + $_weeeTaxAmount, true, false);
                                    else:
                                            if ($_finalPrice == $_price):
                                                $regular['value'] = $_coreHelper->currency($_price, true, false);
                                                $regular['value_org'] = $_coreHelper->currency($_price, false, false);
                                            else:
                                                $regular['value'] = $_coreHelper->currency($_finalPrice, true, false);
                                                $regular['value_org'] = $_coreHelper->currency($_finalPrice, false, false);
                                            endif;
                                    endif;
                            endif;
                            else: /* if ($_finalPrice == $_price): */
                                    $_originalWeeeTaxAmount = $_weeeHelper->getOriginalAmount($_product);

                                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including
                                            $regular['old_price'] = array(
                                                    'label'	=>	Mage::helper('catalog')->__('Regular Price:'),
                                                    'value'	=>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false),
                                                    'value_org'	=>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, false, false)
                                            );
                                            $specialPrice = array();
                                            if ($_taxHelper->displayBothPrices()):
                                                    $specialExc = array();$specialInc = array();

                                                    $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');
                                                    $specialExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                                    $specialExc['value'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false);
                                                    $specialExc['value_org'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, false, false);

                                                    $specialInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                                    $specialInc['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                                    $specialInc['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);

                                                    $specialPrice['exc'] = $specialExc;
                                                    $specialPrice['inc'] = $specialInc;
                                            else:
                                                    $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');
                                                    $specialPrice['value'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmountInclTaxes, true, false);
                                                    $specialPrice['value_org'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmountInclTaxes, false, false);
                                            endif;
                                            $regular['special_price'] = $specialPrice;
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee
                                            $regular['old_price'] = array(
                                                'label'     =>	Mage::helper('catalog')->__('Regular Price:'),
                                                'value'     =>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false),
                                                'value_org' =>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, false, false)
                                            );
                                            $specialExc = array();$specialInc = array();$specialPrice = array();$specialExcWeeString = '';
                                            $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');

                                            $specialExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $specialExc['value'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false);
                                            $specialExc['value_org'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, false, false);

                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $specialExcWeeString .= $_weeeSeparator;
                                                    $specialExcWeeString .= $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $specialExc['weee']	= '('.$specialExcWeeString.')';

                                            $specialInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $specialInc['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $specialInc['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);

                                            $specialPrice['exc'] = $specialExc;
                                            $specialPrice['inc'] = $specialInc;
                                            $regular['special_price'] = $specialPrice;
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee
                                            $regular['old_price'] = array(
                                                'label'     =>	Mage::helper('catalog')->__('Regular Price:'),
                                                'value'     =>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false),
                                                'value_org' =>	$_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, false, false)
                                            );

                                            $specialExc = array();$specialInc = array();$specialPrice = array();$specialExcWeeString = '';
                                            $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');

                                            $specialExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $specialExc['value'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false);
                                            $specialExc['value_org'] = $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, false, false);

                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $specialExcWeeString.= $_weeeSeparator;
                                                    $specialExcWeeString .= $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $specialExc['weee']	= '('.$specialExcWeeString.')';

                                            $specialInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $specialInc['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $specialInc['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);

                                            $specialPrice['exc'] = $specialExc;
                                            $specialPrice['inc'] = $specialInc;
                                            $regular['special_price'] = $specialPrice;
                                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final
                                            $regular['old_price'] = array(
                                                'label'	=>	Mage::helper('catalog')->__('Regular Price:'),
                                                'value'	=>	$_coreHelper->currency($_regularPrice, true, false),
                                                'value_org'	=>	$_coreHelper->currency($_regularPrice, false, false)
                                            );

                                            $specialExc = array();$specialInc = array();$specialPrice = array();$specialExcWeeArray = array();
                                            $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');

                                            $specialExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $specialExc['value'] = $_coreHelper->currency($_finalPrice, true, false);
                                            $specialExc['value_org'] = $_coreHelper->currency($_finalPrice, false, false);

                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    $specialExcWeeArray[] = $_weeeTaxAttribute->getName().': '.$_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                            endforeach;
                                            $specialExc['weee'] = $specialExcWeeArray;

                                            $specialInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $specialInc['value'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false);
                                            $specialInc['value_org'] = $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, false, false);

                                    else: // excl.
                                            $regular['old_price'] = array(
                                                'label'	=>	Mage::helper('catalog')->__('Regular Price:'),
                                                'value'	=>	$_coreHelper->currency($_regularPrice, true, false),
                                                'value_org'	=>	$_coreHelper->currency($_regularPrice, false, false)
                                            );

                                            $specialPrice = array();
                                            $specialPrice['label'] = Mage::helper('catalog')->__('Special Price:');
                                            if ($_taxHelper->displayBothPrices()):
                                                $specialExc = array();$specialInc = array();

                                                $specialExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                                $specialExc['value'] = $_coreHelper->currency($_finalPrice, true, false);
                                                $specialExc['value_org'] = $_coreHelper->currency($_finalPrice, false, false);

                                                $specialInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                                $specialInc['value'] = $_coreHelper->currency($_finalPriceInclTax, true, false);
                                                $specialInc['value_org'] = $_coreHelper->currency($_finalPriceInclTax, false, false);

                                                $specialPrice['exc'] = $specialExc;
                                                $specialPrice['inc'] = $specialInc;
                                            else:
                                                $specialPrice['value'] = $_coreHelper->currency($_finalPrice, true, false);
                                                $specialPrice['value_org'] = $_coreHelper->currency($_finalPrice, false, false);
                                            endif;

                                            $regular['special_price'] = $specialPrice;
                                    endif;

                            endif; /* if ($_finalPrice == $_price): */

                            if ($displayMinimalPrice && $_minimalPriceValue && $_minimalPriceValue < $_product->getFinalPrice()):
                                    $_minimalPriceDisplayValue = $_minimalPrice;
                                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, array(0, 1, 4))):
                                            $_minimalPriceDisplayValue = $_minimalPrice + $_weeeTaxAmount;
                                    endif;
                                    $minimal['label'] = Mage::helper('catalog')->__('As low as:');
                                    $minimal['value'] =  $_coreHelper->currency($_minimalPriceDisplayValue, true, false);
                                    $minimal['value_org'] =  $_coreHelper->currency($_minimalPriceDisplayValue, false, false);
                            endif; /* if ($this->getDisplayMinimalPrice() && $_minimalPrice && $_minimalPrice < $_finalPrice): */

                    else: /* if (!$_product->isGrouped()): */
                            $_exclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue);
                            $_inclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue, true);
                            if ($displayMinimalPrice && $_minimalPriceValue):
                                    $minimalExc = array();$minimalInc = array();
                                    $minimal['label'] = Mage::helper('catalog')->__('Starting at:');
                                    if ($_taxHelper->displayBothPrices()):

                                            $minimalExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $minimalExc['value'] = $_coreHelper->currency($_exclTax, true, false);
                                            $minimalExc['value_org'] = $_coreHelper->currency($_exclTax, false, false);

                                            $minimalInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $minimalInc['value'] = $_coreHelper->currency($_inclTax, true, false);
                                            $minimalInc['value_org'] = $_coreHelper->currency($_inclTax, false, false);

                                    $minimal['exc'] = $minimalExc;
                                    $minimal['inc'] = $minimalInc;
                                    else:
                                            $_showPrice = $_inclTax;
                                            if (!$_taxHelper->displayPriceIncludingTax()) {
                                                    $_showPrice = $_exclTax;
                                            }
                                            $minimal['value'] = $_coreHelper->currency($_showPrice, true, false);
                                            $minimal['value_org'] = $_coreHelper->currency($_showPrice, false, false);
                                    endif;
                            endif; /* if ($this->getDisplayMinimalPrice() && $_minimalPrice): */
                    endif; /* if (!$_product->isGrouped()): */
                    return array(
                            'inc'		=>	$incTax,
                            'exc'		=>	$excludeTax,
                            'regular'	=>	$regular,
                            'minimal'	=>	$minimal
                    );
            } else {
                    /* Bundle product */
                    $_priceModel  = $_product->getPriceModel();
                    list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($_product, null, null, false);
                    list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($_product, null, true, false);
                    $_id = $_product->getId();

                    $_weeeTaxAmount = 0;

                    if ($_product->getPriceType() == 1) {
                            $_weeeTaxAmount = Mage::helper('weee')->getAmount($_product);
                            $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
                            if (Mage::helper('weee')->isTaxable()) {
                                    $_attributes = Mage::helper('weee')->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
                                    $_weeeTaxAmountInclTaxes = Mage::helper('weee')->getAmountInclTaxes($_attributes);
                            }
                            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, array(0, 1, 4))) {
                                    $_minimalPriceTax += $_weeeTaxAmount;
                                    $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                            }
                            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                                    $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                            }

                            if (Mage::helper('weee')->typeOfDisplay($_product, array(1, 2, 4))) {
                                    $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForDisplay($_product);
                            }
                    }
                    $minimal = array();$minimalExc = array();$minimalInc = array();$minimalWeeeString = '';$priceFrom = array();$priceTo = array();
                    $fromExc = array();$fromInc = array();
                    $toExc = array();$toInc = array();
                    if($_product->getPriceView()):
                            $minimal['label'] = Mage::helper('bundle')->__('As low as').':';
                            if ($this->displayBothPricesBundle($_product)):
                                    $minimalExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                    $minimalExc['value'] = Mage::helper('core')->currency($_minimalPriceTax, true, false);
                                    $minimalExc['value_org'] = Mage::helper('core')->currency($_minimalPriceTax, false, false);
                                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                            $_weeeSeparator = '';
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                            $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                    else:
                                                            $amount = $_weeeTaxAttribute->getAmount();
                                                    endif;

                                                    $minimalWeeeString .= $_weeeSeparator;
                                                    $minimalWeeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $minimalExc['weee']	.= '('.$minimalWeeeString.')';
                                    endif;
                                    $minimalInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                    $minimalInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax, true, false);
                                    $minimalInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax, false, false);
                                    $minimal['inc'] = $minimalInc;
                                    $minimal['exc'] = $minimalExc;
                            else:
                                    $minimal['value'] = Mage::helper('core')->currency($_minimalPriceTax, true, false);
                                    $minimal['value_org'] = Mage::helper('core')->currency($_minimalPriceTax, false, false);
                                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                            $_weeeSeparator = '';
                                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                    if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                            $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                    else:
                                                            $amount = $_weeeTaxAttribute->getAmount();
                                                    endif;

                                                    $minimalWeeeString .= $_weeeSeparator;
                                                    $minimalWeeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                    $_weeeSeparator = ' + ';
                                            endforeach;
                                            $minimal['weee']	.= '('.$minimalWeeeString.')';
                                    endif;

                                    if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                                        $minimalInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax, true, false);
                                        $minimalInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax, false, false);
                                    endif;
                                    $minimal['inc'] = $minimalInc;
                            endif;
                    else:
                            if ($_minimalPriceTax <> $_maximalPriceTax):
                                    $priceFrom['label'] = Mage::helper('bundle')->__('From').':';
                                    if ($this->displayBothPricesBundle($_product)):
                                            $fromExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $fromExc['value'] = Mage::helper('core')->currency($_minimalPriceTax,true,false);
                                            $fromExc['value_org'] = Mage::helper('core')->currency($_minimalPriceTax,false,false);

                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $fromExcWeeString = '';
                                                    $_weeeSeparator = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $fromExcWeeString .= $_weeeSeparator;
                                                            $fromExcWeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                                    $fromExc['weee'] = '('.$fromExcWeeString.')';
                                            endif;
                                            $fromInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $fromInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax,true,false);
                                            $fromInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax,false,false);

                                            $priceFrom['exc'] = $fromExc;
                                            $priceFrom['inc'] = $fromInc;
                                    else:
                                            $priceFrom['value'] = Mage::helper('core')->currency($_minimalPriceTax,true,false);
                                            $priceFrom['value_org'] = Mage::helper('core')->currency($_minimalPriceTax,false,false);
                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $fromExcWeeString = '';$_weeeSeparator = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $fromExcWeeString .= $_weeeSeparator;
                                                            $fromExcWeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                                    $priceFrom['weee'] = '('.$fromExcWeeString.')';
                                            endif;
                                            if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                                                $fromInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax,true,false);
                                                $fromInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax,false,false);
                                            endif;
                                            $priceFrom['exc'] = $fromExc;
                                            $priceFrom['inc'] = $fromInc;
                                    endif;

                                    if ($_product->getPriceType() == 1) {
                                            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, array(0, 1, 4))) {
                                                    $_maximalPriceTax += $_weeeTaxAmount;
                                                    $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
                                            }
                                            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                                                    $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
                                            }
                                    }

                                    $priceTo['label'] = Mage::helper('bundle')->__('To').':';
                                    if ($this->displayBothPricesBundle($_product)):
                                            $toExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $toExc['value'] = Mage::helper('core')->currency($_maximalPriceTax, true, false);
                                            $toExc['value_org'] = Mage::helper('core')->currency($_maximalPriceTax, false, false);
                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $_weeeSeparator = '';$toExcWeeString = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $toExcWeeString .= $_weeeSeparator;
                                                            $toExcWeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                                    $toExc['weee'] = '('.$toExcWeeString.')';
                                            endif;

                                            $toInc['label'] = Mage::helper('tax')->__('Incl. Tax');
                                            $toInc['value'] = Mage::helper('core')->currency($_maximalPriceInclTax,true,false);
                                            $toInc['value_org'] = Mage::helper('core')->currency($_maximalPriceInclTax,false,false);

                                            $priceTo['exc'] = $toExc;
                                            $priceTo['inc'] = $toInc;
                                    else:
                                            $priceTo['value'] = Mage::helper('core')->currency($_maximalPriceTax,true,false);
                                            $priceTo['value_org'] = Mage::helper('core')->currency($_maximalPriceTax,false,false);
                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $_weeeSeparator = '';$toExcWeeString = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $toExcWeeString .= $_weeeSeparator;
                                                            $toExcWeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                                    $priceTo['weee'] = '('.$toExcWeeString.')';
                                            endif;
                                            if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                                                $toInc['value'] = Mage::helper('core')->currency($_maximalPriceInclTax, true, false);
                                                $toInc['value_org'] = Mage::helper('core')->currency($_maximalPriceInclTax, false, false);
                                            endif;

                                            $priceTo['exc'] = $toExc;
                                            $priceTo['inc'] = $toInc;
                                    endif;
                            else:
                                    if($this->displayBothPricesBundle($_product)):
                                            $minimalExc['label'] = Mage::helper('tax')->__('Excl. Tax:');
                                            $minimalExc['value'] = Mage::helper('core')->currency($_minimalPriceTax, true, false);
                                            $minimalExc['value_org'] = Mage::helper('core')->currency($_minimalPriceTax, false, false);
                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $_weeeSeparator = '';$toExcWeeString = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $toExcWeeString .= $_weeeSeparator;
                                                            $toExcWeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                            endif;
                                            $minimalInc['label'] = Mage::helper('tax')->__('Incl. Tax:');
                                            $minimalInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax,true,false);
                                            $minimalInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax,false,false);

                                            $minimal['exc'] = $minimalExc;
                                            $minimal['inc'] = $minimalInc;
                                    else:
                                            $minimal['value'] = Mage::helper('core')->currency($_minimalPriceTax, true, false);
                                            $minimal['value_org'] = Mage::helper('core')->currency($_minimalPriceTax, false, false);
                                            if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array(2, 1, 4))):
                                                    $_weeeSeparator = '';$minimalWeeeString = '';
                                                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute):
                                                            if (Mage::helper('weee')->typeOfDisplay($_product, array(2, 4))):
                                                                    $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                                                            else:
                                                                    $amount = $_weeeTaxAttribute->getAmount();
                                                            endif;

                                                            $minimalWeeeString .= $_weeeSeparator;
                                                            $minimalWeeeString .= $_weeeTaxAttribute->getName().': '.Mage::helper('core')->currency($amount, true, false);
                                                            $_weeeSeparator = ' + ';
                                                    endforeach;
                                                    $minimal['weee'] = '('.$minimalWeeeString.')';
                                            endif;
                                            if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                                                $minimalInc['value'] = Mage::helper('core')->currency($_minimalPriceInclTax,true,false);
                                                $minimalInc['value_org'] = Mage::helper('core')->currency($_minimalPriceInclTax,false,false);
                                            endif;
                                            $minimal['exc'] = $minimalExc;
                                            $minimal['inc'] = $minimalInc;
                                    endif;
                            endif;
                    endif;
                    return array(
                            'minimal'		=>	$minimal,
                            'price_from'	=>	$priceFrom,
                            'price_to'		=>	$priceTo
                    );

            }
    }
	
	/**
     * Check if we have display prices including and excluding tax
     * With corrections for Dynamic prices
     *
     * @return bool
     */
    public function displayBothPricesBundle($product)
    {
        if ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC &&
            $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false) {
            return false;
        }
        return Mage::helper('tax')->displayBothPrices();
    }

    /**
     * Get Price configured for bundle product
     *
     * @param Mage_Catalog_Model_Product $_product
     * @return array
     */
    public function getPriceConfigured($_product){
        //$_finalPrice = $_product->getFinalPrice();
        //$_finalPriceInclTax = $_product->getFinalPrice();
        $canApplyMAP  = Mage::helper('catalog')->canApplyMsrp($_product);
        $taxHelper = Mage::helper('tax');
        if ($_product->getCanShowPrice() !== false){
            $result = array();
            $result['title'] = Mage::helper('bundle')->__('Price as configured');

            if($taxHelper->displayBothPrices()){

                /* Get price exclude tax */
                $exc = array();
                /*if (!$canApplyMAP){echo $_finalPrice;
                    $exc['value']       = Mage::helper('core')->currency($_finalPrice,true,false);
                    $exc['value_org']   = Mage::helper('core')->currency($_finalPrice,false,false);
                } else {
                    $exc['value'] = 0;
                    $exc['value_org'] = 0;
                }*/
                $exc['label'] = $taxHelper->__('Excl. Tax:');
                $result['exc'] = $exc;

                /* Get price include tax */
                $inc = array();
                /*if (!$canApplyMAP){
                    $exc['value']       = Mage::helper('core')->currency($_finalPriceInclTax,true,false);
                    $exc['value_org']   = Mage::helper('core')->currency($_finalPriceInclTax,false,false);
                } else {
                    $exc['value'] = 0;
                    $exc['value_org'] = 0;
                }*/
                $inc['label'] = $taxHelper->__('Incl. Tax:');
                $result['inc'] = $inc;
            } else {
                /*$regular = array();
                if (!$canApplyMAP){
                    $regular['value'] = Mage::helper('core')->currency($_finalPrice,true,false);
                    $regular['value_org'] = Mage::helper('core')->currency($_finalPrice,false,false);
                } else {
                    $regular['value'] = 0;
                    $regular['value_org'] = 0;
                }
                $result['regular'] = $regular;*/
            }
            return $result;
        }
        return array();
    }

    public function showTierPrice($_product){
        $_tierPrices = $this->getTierPrices($_product);

        $_finalPriceInclTax = $this->helper('tax')->getPrice($_product, $_product->getFinalPrice(), true);

        /** @var $_catalogHelper Mage_Catalog_Helper_Data */
        $_catalogHelper = Mage::helper('catalog');

        $_weeeTaxAmount = Mage::helper('weee')->getAmountForDisplay($_product);
        if (Mage::helper('weee')->typeOfDisplay($_product, array(1,2,4))) {
            $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForDisplay($_product);
        }
        $result = array();
        if (count($_tierPrices) > 0){
            foreach ($_tierPrices as $_index => $_price){
                $item = '';
                if ($_catalogHelper->canApplyMsrp($_product)):
                    if($_product->isGrouped()){
                        $item .= $_catalogHelper->__('Buy %1$s for', $_price['price_qty']);
                    } else {
                        $item .= $_catalogHelper->__('Buy %1$s', $_price['price_qty']);
                    }
                else:
                    if ($this->helper('tax')->displayBothPrices()):
                        if (Mage::helper('weee')->typeOfDisplay($_product, 0)):
                            $item .= $_catalogHelper->__('Buy %1$s for %2$s (%3$s incl. tax) each', $_price['price_qty'], $_price['formated_price_incl_weee_only'], $_price['formated_price_incl_weee']);
                        elseif(Mage::helper('weee')->typeOfDisplay($_product, 1)):
                            $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            if ($_weeeTaxAttributes):
                                $item .= '(';
                                $item .= $_catalogHelper->__('%1$s incl tax.', $_price['formated_price_incl_weee']);
                                $separator = ' + ';
                                foreach ($_weeeTaxAttributes as $_attribute):
                                    $item .= $separator;
                                    $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                endforeach;
                                $item .= ')';
                            endif;
                            $item .= $_catalogHelper->__('each');
                        elseif(Mage::helper('weee')->typeOfDisplay($_product, 4)):
                            $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            if ($_weeeTaxAttributes):
                                $item .= '(';
                                $item .= $_catalogHelper->__('%1$s incl tax.', $_price['formated_price_incl_weee']);
                                $separator = ' + ';
                                foreach ($_weeeTaxAttributes as $_attribute):
                                    $item .= $separator;
                                    $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount()+$_attribute->getTaxAmount());
                                endforeach;
                                $item .= ')';
                            endif;
                            $item .= $_catalogHelper->__('each');
                        elseif(Mage::helper('weee')->typeOfDisplay($_product, 2)):
                            $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price']);
                            if ($_weeeTaxAttributes):
                                $item .= '(';
                                foreach ($_weeeTaxAttributes as $_attribute):
                                    $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                endforeach;
                                $item .= $_catalogHelper->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee']);
                                $item .= ')';
                            endif;
                            $item .= $_catalogHelper->__('each');
                        else:
                            $item .= $_catalogHelper->__('Buy %1$s for %2$s (%3$s incl. tax) each', $_price['price_qty'], $_price['formated_price'], $_price['formated_price_incl_tax']);
                        endif;
                    else:
                        if ($this->helper('tax')->displayPriceIncludingTax()):
                            if (Mage::helper('weee')->typeOfDisplay($_product, 0)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_weee']);
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 1)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $separator;
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                        $separator = ' + ';
                                    endforeach;
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 4)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $separator;
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount()+$_attribute->getTaxAmount());
                                        $separator = ' + ';
                                    endforeach;
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 2)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_tax']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                    endforeach;
                                    $item .= $_catalogHelper->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee']);
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            else:
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_tax']);
                            endif;
                        else:
                            if (Mage::helper('weee')->typeOfDisplay($_product, 0)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 1)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $separator;
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                        $separator = ' + ';
                                    endforeach;
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 4)):
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $separator;
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount()+$_attribute->getTaxAmount());
                                        $separator = ' + ';
                                    endforeach;
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            elseif(Mage::helper('weee')->typeOfDisplay($_product, 2)):
                                $item.= $_catalogHelper->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price']);
                                if ($_weeeTaxAttributes):
                                    $item .= '(';
                                    foreach ($_weeeTaxAttributes as $_attribute):
                                        $item .= $_attribute->getName().': '.Mage::helper('core')->currency($_attribute->getAmount());
                                    endforeach;
                                    $item .= $_catalogHelper->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee_only']);
                                    $item .= ')';
                                endif;
                                $item .= $_catalogHelper->__('each');
                            else:
                                $item .= $_catalogHelper->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price']);
                            endif;
                        endif;
                    endif;
                endif; // Can apply MSRP

                if (!$_product->isGrouped()):
                    if(($_product->getPrice() == $_product->getFinalPrice() && $_product->getPrice() > $_price['price'])
                        || ($_product->getPrice() != $_product->getFinalPrice() &&  $_product->getFinalPrice() > $_price['price'])):
                        $item .= ' '.$_catalogHelper->__('and').' '.$_catalogHelper->__('save').' '.$_price['savePercent'].'%';
                    endif;
                endif;
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Get tier prices (formatted)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            return array();
        }
        $prices  = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty'] * 1;

                $_productPrice = $product->getPrice();
                if ($_productPrice != $product->getFinalPrice()) {
                    $_productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();
                if ($_productPrice > $groupPrice) {
                    $_productPrice = $groupPrice;
                }

                if ($price['price'] < $_productPrice) {
                    $price['savePercent'] = ceil(100 - ((100 / $_productPrice) * $price['price']));

                    $tierPrice = $this->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['website_price'])
                    );
                    $price['formated_price'] = $this->getStore()->formatPrice($tierPrice,false,false);
                    $price['formated_price_incl_tax'] = $this->getStore()->formatPrice(
                        $this->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['website_price'], true)
                        ),false,false
                    );

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        //$this->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }

        return $res;
    }

    public function helper($name){
        return Mage::helper($name);
    }
}
?>