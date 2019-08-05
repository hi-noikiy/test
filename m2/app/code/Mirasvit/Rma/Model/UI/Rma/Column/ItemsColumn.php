<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\UI\Rma\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ItemsColumn extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->rmaSearchManagement = $rmaSearchManagement;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param string $value
     * @return string
     */
    private function getLocalizedValue($value)
    {
        if ($serialized = @unserialize($value)) {
            return array_values($serialized)[0];
        }
        return $value;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $rma = $objectManager->create('\Mirasvit\Rma\Model\Rma')->load($item['rma_id']);
                $items = $this->rmaSearchManagement->getRequestedItems($rma);
                $s = '';
                foreach ($items as $currentItem) {
                    $orderItem = $this->orderItemRepository->get($currentItem->getOrderItemId());

                    $s .= '<b>' . $orderItem->getName() . '</b>';
                    $s .= ' / ';
                    $s .= $currentItem->getReasonName() ?
                        $this->getLocalizedValue($currentItem->getReasonName()) : '-';
                    $s .= ' /  ';
                    $s .= $currentItem->getConditionName() ?
                        $this->getLocalizedValue($currentItem->getConditionName()) : '-';
                    $s .= ' / ';
                    $s .= $currentItem->getResolutionName() ?
                        $this->getLocalizedValue($currentItem->getResolutionName()) : '-';
                    $s .= '<br>';
                }

                $item[$name] = $s;

            }
        }

        return $dataSource;
    }
}