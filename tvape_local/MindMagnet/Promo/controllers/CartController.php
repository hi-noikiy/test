<?php

require_once(Mage::getModuleDir('controllers','Amasty_Promo').DS.'CartController.php');

class MindMagnet_Promo_CartController extends Amasty_Promo_CartController
{

    public function updateAction()
    {
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();

        $productId = $this->getRequest()->getParam('product_id');

        $product = Mage::getModel('catalog/product')->load($productId);



        if ($product->getId())
        {
            $limits  = Mage::getSingleton('ampromo/registry')->getLimits();

            $sku = $product->getSku();

            $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
            $addOneRule = false;

            if (!$addAllRule)
            {

                foreach ($rules as $rule) {
                    if ($rule->getSimpleAction() == 'ampromo_items') {
                        $skuArray = explode(",",$rule->getPromoSku());
                        if (in_array($sku,$skuArray)) {
                            $addOneRule = $rule->getId();
                            break;
                        }
                    }
                }

            /**
                //original loop from amasty_promo extension

                foreach ($limits['_groups'] as $ruleId => $rule)
                {
                    if (in_array($sku, $rule['sku']))
                    {
                        $addOneRule = $ruleId;
                    }
                }
             *  */

            } else if (isset($limits[$sku])){

                $addOneRule = $limits[$sku]['rule_id'];
            }

            if ($addAllRule || $addOneRule)
            {
            $super = $this->getRequest()->getParam('super_attributes');
            $options = $this->getRequest()->getParam('options');
            $bundleOptions = $this->getRequest()->getParam('bundle_option');
            $downloadableLinks = $this->getRequest()->getParam('links');

            /* To compatibility amgiftcard module */
            $amgiftcardValues = array();

            if($product->getTypeId() == 'amgiftcard') {
                $amgiftcardFields = array_keys(Mage::helper('amgiftcard')->getAmGiftCardFields());
                foreach($amgiftcardFields as $amgiftcardField) {
                    if($this->getRequest()->getParam($amgiftcardField)) {
                        $amgiftcardValues[$amgiftcardField] = $this->getRequest()->getParam($amgiftcardField);
                    }
                }
            }


            $params = $this->getRequest()->getParams();

            Mage::helper('ampromo')->addProduct($product, $super, $options, $bundleOptions, $addOneRule, $amgiftcardValues, 1, $downloadableLinks, $params);
            }
        }

    }
}
