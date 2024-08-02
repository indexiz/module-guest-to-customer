<?php
declare(strict_types=1);

namespace Indexiz\GuestToCustomer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class AssignOrder
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $order
     * @param $customerId
     */
    public function saveOrder($order, $customerId)
    {
        $orderId = $order->getId() ?? null;
        /** @var Order $order */
        if (isset($orderId)) {
            $order = $this->orderRepository->get($orderId);
            if ($order->getId() && !$order->getcustomerId()) {
                $order->setCustomerId($customerId);
                $order->setCustomerGroupId($this->customerRepository->getById($customerId)->getGroupId());
                $order->setCustomerIsGuest('0');
                $this->orderRepository->save($order);
            }
        }
    }
}
