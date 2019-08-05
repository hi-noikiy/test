<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Plugin;

use Magento\Framework\Exception\AuthenticationException;

class UserAuth
{

    /**
     * @var \Amasty\SecurityAuth\Model\AuthRepository
     */
    protected $authRepository;

    /**
     * @var \Amasty\SecurityAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $userModel;

    /**
     * AuthLoginAfter constructor.
     * @param \Amasty\SecurityAuth\Model\AuthRepository $authRepository
     * @param \Amasty\SecurityAuth\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     */
    public function __construct(
        \Amasty\SecurityAuth\Model\AuthRepository $authRepository,
        \Amasty\SecurityAuth\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\User\Model\User $userModel
    ) {
        $this->authRepository = $authRepository;
        $this->helper = $helper;
        $this->session = $session;
        $this->auth = $auth;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->backendHelper = $backendHelper;
        $this->request = $request;
        $this->userModel = $userModel;
    }

    /**
     * @param \Magento\Backend\Model\Auth\Interceptor $subject
     * @param $username
     * @param $password
     * @throws AuthenticationException
     */
    public function beforeLogin(
        \Magento\Backend\Model\Auth\Interceptor $subject,
        $username,
        $password
    ) {
        if ($userId = $this->userModel->loadByUsername($username)->getId()) {
            $userAuth = $this->authRepository->getByUserId($userId);

            if ($userAuth->getUserId()
                && $userId
                && $this->helper->isActive()
                && $userAuth->getEnable()
            ) {
                $code = $this->request->getParam(\Amasty\SecurityAuth\Helper\Data::CODE_NAME_FOR_INPUT_FORM);
                if (!$this->helper->verifyCode($userAuth->getTwoFactorToken(), $code)) {
                    throw new AuthenticationException(__('Security Code is Incorrect.'));
                }
            }
        }
    }
}
