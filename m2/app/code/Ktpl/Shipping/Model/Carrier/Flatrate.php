<?php

namespace Ktpl\Shipping\Model\Carrier;

/**
 * Flat rate shipping model
 *
 * @api
 * @since 100.0.2
 */
class Flatrate extends \Magento\OfflineShipping\Model\Carrier\Flatrate {

    /**
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        if ($customerSession->isLoggedIn()) {

            $customerGroupId = $customerSession->getCustomerGroupId();
            $cg = $scopeConfig->getValue('ktpl_wholesaler_section/wholesale/customergroup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $allowesGroup = explode(',', $cg);

            $total = $request->getBaseSubtotalInclTax();

            $maxTotal = $this->getConfigData('max_order_total');

            if (!empty($maxTotal) && (in_array($customerGroupId, $allowesGroup)) && ($total > $maxTotal)) {
                return false;
            }
        }
        return parent :: collectRates($request);
    }

}
