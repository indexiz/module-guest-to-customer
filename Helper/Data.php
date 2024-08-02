<?php
declare(strict_types=1);

namespace Indexiz\GuestToCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Function to check if extension enabled
     *
     * @return bool
     */
    public function isGuestToCustomerEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'indexiz_guest_to_customer/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to check if guest orders should be converted to customer orders
     *
     * @return bool
     */
    public function isAutoOrderConvertEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'indexiz_guest_to_customer/general/auto_order_convert',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * How many guests to be converted into customers per time
     *
     * @return mixed
     */
    public function getCronBatchSize()
    {
        return $this->scopeConfig->getValue(
            'indexiz_guest_to_customer/schedule/batch_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultGroupZero()
    {
        return $this->scopeConfig->getValue(
            'indexiz_guest_to_customer/general/default_group_zero',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultGroup()
    {
        return $this->scopeConfig->getValue(
            'indexiz_guest_to_customer/general/default_group',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to check if welcome email shoud be sent to customer
     *
     * @return bool
     */
    public function isEmailEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'indexiz_guest_to_customer/general/send_email',
            ScopeInterface::SCOPE_STORE
        );
    }
}
