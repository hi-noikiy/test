<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */

namespace Fsv\CustomerManager\Plugin\Customer\Controller\Adminhtml\Index;

use Closure;
use Magento\Customer\Controller\Adminhtml\Index\Save as CustomerSave;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\User\Model\User;
use Magento\Framework\Registry;
use Fsv\CustomerManager\Helper\Data as CustomerManagerHelper;

/**
 * Fsv\CustomerManager\Plugin\Customer\Controller\Adminhtml\Index\Save
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class Save
{
    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * ManagerInterface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Session
     *
     * @var Session
     */
    protected $session;

    /**
     * User
     *
     * @var User
     */
    protected $user;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Save constructor.
     *
     * @param Request $request
     * @param ManagerInterface $messageManager
     * @param Session $session
     * @param User $user
     * @param RedirectFactory $resultRedirectFactory
     * @param Registry $registry
     */
    public function __construct(
        Request $request,
        ManagerInterface $messageManager,
        Session $session,
        User $user,
        RedirectFactory $resultRedirectFactory,
        Registry $registry
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->user = $user;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->registry = $registry;
    }

    /**
     * Check ability to change password
     *
     * @param CustomerSave $subject
     * @param Closure $proceed
     * @return Redirect
     */
    public function aroundExecute(CustomerSave $subject, Closure $proceed)
    {
        $postData = $this->request->getPostValue('customer');
        $customerId = isset($postData['entity_id']) ? $postData['entity_id'] : '';

        if (!isset($postData['password']) || $postData['password'] === '') {
            return $proceed();
        }

        $adminId = $this->session->getUser()->getId();
        $adminUser = $this->user->load($adminId);

        if (!isset($postData['admin_password']) || !$adminUser->verifyIdentity($postData['admin_password'])) {
            $this->messageManager->addErrorMessage(__('Invalid password of the current administrator.'));

            $resultRedirect = $this->resultRedirectFactory->create();

            if ($customerId) {
                $resultRedirect->setPath(
                    'customer/*/edit',
                    ['id' => $customerId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'customer/*/new',
                    ['_current' => true]
                );
            }

            return $resultRedirect;
        }

        $this->registry->register(CustomerManagerHelper::CHANGE_PASSWORD_FLAG, true);

        return $proceed();
    }
}