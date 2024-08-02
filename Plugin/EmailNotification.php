<?php

namespace Indexiz\GuestToCustomer\Plugin;

use Indexiz\GuestToCustomer\Helper\Data;

class EmailNotification
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    public function aroundNewAccount(\Magento\Customer\Model\EmailNotification $subject, \Closure $proceed)
    {
        if ($this->helperData->isEmailEnabled()) {
            $proceed();
        }
        return $subject;
    }
}
