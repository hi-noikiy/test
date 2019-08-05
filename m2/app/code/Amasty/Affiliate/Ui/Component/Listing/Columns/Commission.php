<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Ui\Component\Listing\Columns;

class Commission extends \Magento\Catalog\Ui\Component\Listing\Columns\Price
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam(
                    'store_id',
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            $fieldName = $this->getData('name');

            if ($fieldName == 'commission_value') {
                $typeField = 'commission_value_type';
            } elseif ($fieldName == 'discount_amount') {
                $typeField = 'discount_type';
            } elseif ($fieldName == 'commission_value_second') {
                $typeField = 'commission_type_second';
            }

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    if ($fieldName == 'commission_value_second') {
                        if (!$item['from_second_order']
                            || $item['withdrawal_type'] == \Amasty\Affiliate\Model\Transaction::TYPE_PER_PROFIT
                        ) {
                            $item['commission_value_second'] = $item['commission_value'];
                            continue;
                        }
                    }

                    if ($item[$typeField] == \Amasty\Affiliate\Model\Program::COMMISSION_TYPE_FIXED) {
                        $item[$fieldName] = $currency->toCurrency(sprintf("%f", $item[$fieldName]));
                    } else {
                        $item[$fieldName] = number_format($item[$fieldName], 2) . '%';
                    }
                }
            }
        }

        return $dataSource;
    }
}
