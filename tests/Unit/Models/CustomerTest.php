<?php

namespace Tests\Unit\Models;

use Modules\Sales\app\Models\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private function makeCustomer(float $limit, float $current): Customer
    {
        $c = new Customer();
        $c->credit_limit   = $limit;
        $c->current_credit = $current;
        return $c;
    }

    public function test_available_credit_returns_remainder(): void
    {
        $customer = $this->makeCustomer(limit: 100000, current: 30000);

        $this->assertEquals(70000, $customer->availableCredit());
    }

    public function test_available_credit_returns_zero_when_at_limit(): void
    {
        $customer = $this->makeCustomer(limit: 100000, current: 100000);

        $this->assertEquals(0, $customer->availableCredit());
    }

    public function test_available_credit_clamps_to_zero_when_over_limit(): void
    {
        $customer = $this->makeCustomer(limit: 100000, current: 120000);

        $this->assertEquals(0, $customer->availableCredit());
    }

    public function test_has_credit_true_when_in_debt(): void
    {
        $customer = $this->makeCustomer(limit: 50000, current: 15000);

        $this->assertTrue($customer->hasCredit());
    }

    public function test_has_credit_false_when_no_debt(): void
    {
        $customer = $this->makeCustomer(limit: 50000, current: 0);

        $this->assertFalse($customer->hasCredit());
    }

    public function test_available_credit_with_zero_limit(): void
    {
        // limit=0 means no credit facility — available should be 0
        $customer = $this->makeCustomer(limit: 0, current: 0);

        $this->assertEquals(0, $customer->availableCredit());
    }
}
