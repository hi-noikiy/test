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


namespace Mirasvit\Rma\Model\UI\Status;

use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory;
use Mirasvit\Rma\Helper\Storeview;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class StatusDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array $_loadedData
     */
    protected $_loadedData;

    /**
     * AddressDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $statusCollectionFactory
     * @param UrlInterface $url
     * @param RequestInterface $requestInterface
     * @param Storeview $rmaStoreview
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $statusCollectionFactory,
        UrlInterface $url,
        RequestInterface $requestInterface,
        Storeview $rmaStoreview,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $statusCollectionFactory->create();
        $this->url                = $url;
        $this->request            = $requestInterface;
        $this->storeview          = $rmaStoreview;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $config = parent::getConfigData();
        $config['submit_url'] = $this->url->getUrl('*/*/save',
            [
                'id' => (int) $this->request->getParam('id'),
                'store' => (int) $this->request->getParam('store')
            ]
        );
        $config['store_id'] = (int) $this->request->getParam('store');
        return $config;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Mirasvit\Rma\Model\Status $status */
        foreach ($items as $status) {
            $status->setStoreId($this->getConfigData()['store_id']);

            $data = $status->getData();
            $data[StatusInterface::KEY_NAME] =
                $this->storeview->getStoreViewValue($status, StatusInterface::KEY_NAME);

            $data[StatusInterface::KEY_CUSTOMER_MESSAGE] = $status->getCustomerMessage();
            $data[StatusInterface::KEY_ADMIN_MESSAGE]    = $status->getAdminMessage();
            $data[StatusInterface::KEY_HISTORY_MESSAGE]  = $status->getHistoryMessage();

            $data['disabled'] = true;
            $this->_loadedData[$status->getStatusId()] = $data;
        }
        return $this->_loadedData;
    }
}