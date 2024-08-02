<?php
declare(strict_types=1);

namespace Indexiz\GuestToCustomer\Model;

use Indexiz\GuestToCustomer\Helper\Data;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Debug;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CreateCustomer
{
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var AssignOrder
     */
    private $assignOrder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param SessionFactory $sessionFactory
     * @param AssignOrder $assignOrder
     * @param LoggerInterface $logger
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerInterfaceFactory,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        SessionFactory $sessionFactory,
        AssignOrder $assignOrder,
        LoggerInterface $logger,
        Data $helperData
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->sessionFactory = $sessionFactory;
        $this->assignOrder = $assignOrder;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->helperData->isGuestToCustomerEnabled()) {
            $batchSize = $this->helperData->getCronBatchSize() ?? 50;
            $guestOrderCollection = $this->orderCollectionFactory->create()
                ->addAttributeToFilter(OrderInterface::CUSTOMER_ID, ['null' => true])
                ->setPageSize($batchSize);
            $guestOrderCollection->getSelect()->group('customer_email');
            foreach ($guestOrderCollection as $guestOrder) {
                try {
                    $existingCustomer = $this->customerRepository->get($guestOrder->getCustomerEmail());
                    if ($this->helperData->isAutoOrderConvertEnabled()) {
                        $this->assignOrders($existingCustomer->getId(), $existingCustomer->getEmail());
                    }
                    continue;
                } catch (NoSuchEntityException|LocalizedException $exception) {
                    $this->logger->info('Guest to customer conversion starts.');
                }

                try {
                    $customerEmail = $guestOrder->getCustomerEmail();
                    $firstName = $guestOrder->getCustomerFirstname() ?
                        $guestOrder->getCustomerFirstname() : $guestOrder->getShippingAddress()->getFirstname();
                    $lastName = $guestOrder->getCustomerLastname() ?
                        $guestOrder->getCustomerLastname() : $guestOrder->getShippingAddress()->getLastname();
                    $password = $this->generateRandomPassword();
//                    $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
                    $websiteId = $this->storeManager->getStore($guestOrder->getStoreId())->getWebsiteId();
                    $customer = $this->customerInterfaceFactory->create();
                    $customer->setWebsiteId($websiteId);
                    $customer->setEmail($customerEmail);
                    $customer->setFirstname($firstName);
                    $customer->setLastname($lastName);
                    $extensionAttributes = $customer->getExtensionAttributes();
                    $extensionAttributes->setIsSubscribed(false);
                    $customer->setExtensionAttributes($extensionAttributes);
                    $customerSession = $this->sessionFactory->create();
                    $customerSession->regenerateId();
                    $redirectUrl = $customerSession->getBeforeAuthUrl();
                    $customer = $this->accountManagement
                        ->createAccount($customer, $password, $redirectUrl);
                    // Assign customer group to customer based on admin configuration
                    if ($guestOrder->getBaseSubtotal() > 0) {
                        $customer->setGroupId($this->helperData->getDefaultGroup());
                    } else {
                        $customer->setGroupId($this->helperData->getDefaultGroupZero());
                    }
                    $this->customerRepository->save($customer);
                    // Assign orders to customer based on email
                    if ($this->helperData->isAutoOrderConvertEnabled()) {
                        $this->assignOrders($customer->getId(), $customer->getEmail());
                    }
                } catch (StateException $e) {
                    $this->logger->error('A customer account linked to email address ' .
                        $guestOrder->getCustomerEmail() . ' already exit.');
                } catch (InputException $e) {
                    $this->logger->error($e->getMessage());
                    foreach ($e->getErrors() as $error) {
                        $this->logger->error($error->getMessage());
                    }
                } catch (LocalizedException $e) {
                    $this->logger->error($e->getMessage());
                } catch (\Exception $e) {
                    $message = sprintf(
                        'Exception message: %s%sTrace: %s',
                        $e->getMessage(),
                        "\n",
                        Debug::trace(
                            $e->getTrace(),
                            true,
                            true,
                            (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
                        )
                    );
                    $this->logger->critical($message);
                }
            }
        }
    }

    /**
     * Function to generate a random password
     *
     * @return string
     */
    private function generateRandomPassword()
    {
        $random_characters = 3;
        $lower_case = str_shuffle("abcdefghijklmnopqrstuvwxyz");
        $symbols = str_shuffle("!@#$%^&*");
        $upper_case = str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $numbers = str_shuffle("1234567890");
        $random_password = substr($lower_case, 0, $random_characters);
        $random_password .= substr($symbols, 0, $random_characters);
        $random_password .= substr($upper_case, 0, $random_characters);
        $random_password .= substr($numbers, 0, $random_characters);
        return  str_shuffle($random_password);
    }

    /**
     * @param $customerId
     * @param $customerEmail
     * @return void
     */
    public function assignOrders($customerId, $customerEmail)
    {
        if (isset($customerId)) {
            $orderCollection = $this->orderCollectionFactory->create()
                ->addAttributeToFilter('customer_email', $customerEmail)
                ->addAttributeToFilter(OrderInterface::CUSTOMER_ID, ['null' => true]);
            foreach ($orderCollection as $order) {
                $this->assignOrder->saveOrder($order, $customerId);
            }
        }
    }
}
