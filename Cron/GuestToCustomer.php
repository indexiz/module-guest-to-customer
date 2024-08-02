<?php
declare(strict_types=1);

namespace Indexiz\GuestToCustomer\Cron;

use Indexiz\GuestToCustomer\Model\CreateCustomer;

class GuestToCustomer
{
    /**
     * @var CreateCustomer
     */
    private $createCustomer;

    /**
     * @param CreateCustomer $createCustomer
     */
    public function __construct(
        CreateCustomer $createCustomer
    ) {
        $this->createCustomer = $createCustomer;
    }

    /**
     * @return void
     */
    public function execute()
    {
        //Convert guests into customers
        $this->createCustomer->execute();
    }
}
