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

use Mirasvit\Rma\Api\Config\HelpdeskConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ActionColumn extends Column
{
    const URL_PATH_EDIT = 'rma/rma/edit';
    const URL_PATH_DELETE = 'rma/rma/delete';
    const URL_PATH_CONVERT = 'rma/rma/convertTicket';
    const URL_PATH_ORDER = 'sales/order/view';

    /**
     * @var UrlInterface $urlBuilder
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        HelpdeskConfigInterface $helpdeskConfig,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder     = $urlBuilder;
        $this->helpdeskConfig = $helpdeskConfig;

        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                $item[$name] = [];
                if (isset($item['rma_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $item['rma_id']]),
                        'label' => __('Edit')
                    ];
                    if ($this->helpdeskConfig->isHelpdeskActive()) {
                        $item[$name]['convertTicket'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_CONVERT, ['id' => $item['rma_id']]),
                            'label' => __('Convert To Ticket')
                        ];
                    }
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $item['rma_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.name }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.name }" record?')
                        ]
                    ];

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    /** @var \Mirasvit\Rma\Model\Rma $rma */
                    $rma = $objectManager->create('\Mirasvit\Rma\Model\Rma');
                    $rma->getResource()->load($rma, $item[$rma->getIdFieldName()]);
                    $rma->getResource()->afterLoad($rma);
                    $order = $objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($item['order_id']);
                    if ($order) {
                        $item[$name]['order'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_ORDER, ['order_id' => $order->getId()]),
                            'label' => __('View order #${ $.$data.order_id }'),
                            'target' => '_blank'
                        ];
                    }
                    if ($rma->getExchangeOrderIds()) {
                        foreach ($rma->getExchangeOrderIds() as $k => $orderId) {
                            /** @var \Magento\Sales\Model\Order $order */
                            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                            $url   = $this->urlBuilder->getUrl(self::URL_PATH_ORDER, ['order_id' => $order->getId()]);
                            $item[$name]['order'.$k] = [
                                'href'   => $url,
                                'label'  => __('View exchange order %1', '#'.$order->getIncrementId()),
                                'target' => '_blank'
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}