<?php

namespace Cminds\Salesrep\Controller\Adminhtml\Order;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

class ChangeManager extends \Magento\Customer\Controller\Adminhtml\Index
{
    protected $salesrepRepositoryInterface;
    protected $adminUsers;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cminds\Salesrep\Api\SalesrepRepositoryInterface $salesrepRepositoryInterface,
        \Magento\User\Model\ResourceModel\User\Collection $adminUsers
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $customerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $customerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $customerAccountManagement,
            $addressRepository,
            $customerDataFactory,
            $addressDataFactory,
            $customerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
        $this->salesrepRepositoryInterface = $salesrepRepositoryInterface;
        $this->adminUsers = $adminUsers;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            if ($params['orderId']) {
                $salesrep = $this->salesrepRepositoryInterface
                    ->getByOrderId($params['orderId']);

                if ($params['managerId']) {
                    $adminUser = $this->adminUsers
                        ->getItemById($params['managerId']);
                } else {
                    $adminUser = false;
                }

                if ($adminUser) {
                    $managerCommissionEarned = $this
                        ->salesrepRepositoryInterface
                        ->getManagerCommissionEarned(
                            $params['orderId'],
                            $adminUser->getData(
                                'salesrep_manager_commission_rate'
                            ),
                            $salesrep->getRepCommissionEarned()
                        );

                    $salesrep
                        ->setManagerId($adminUser->getId())
                        ->setManagerName(
                            $adminUser->getData('firstname') . ' ' .
                            $adminUser->getData('lastname')
                        );
                    if ($managerCommissionEarned) {
                        $salesrep->setManagerCommissionEarned(
                            $managerCommissionEarned
                        );
                    }

                    if (!$salesrep->getSalesrepId()) {
                        $salesrep->setOrderId($params['orderId']);
                    }
                    $this->salesrepRepositoryInterface->save($salesrep);
                } else {
                    $salesrep
                        ->setManagerId(null)
                        ->setManagerName(null)
                        ->setManagerCommissionEarned(null);
                    $this->salesrepRepositoryInterface->save($salesrep);
                }

                $result = $this->resultJsonFactory->create();

                $result->setData(
                    [
                        'success' => true,
                        'manager_commission' =>
                            $salesrep->getManagerCommissionEarned(),
                        'manager_id' =>
                            $salesrep->getManagerId()
                    ]
                );
                return $result;
            }
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            $result->setData(['success' => false]);
            return $result;
        }
    }
}
