<?php

namespace ItmoSoft\Loyalty\Tests\Feature;

use ItmoSoft\Loyalty\LoyaltyManager;
use ItmoSoft\Loyalty\Tests\Support\User;
use ItmoSoft\Loyalty\Tests\TestCase;

class LoyaltyAccountTest extends TestCase
{
    public function test_it_redeems_points_and_keeps_a_transaction_ledger(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $manager = app(LoyaltyManager::class);
        $manager->earn($user, 100, 'Order completed');
        $tx = $manager->redeem($user, 35, 'Redeemed at checkout');

        $this->assertSame(-35, $tx->points);
        $this->assertSame(65, $tx->balance_after);
        $this->assertSame(65, (int) $user->loyaltyAccount()->first()->balance);
        $this->assertCount(2, $user->loyaltyAccount()->first()->transactions);
    }

    public function test_it_supports_manual_adjustments(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $tx = app(LoyaltyManager::class)->adjust($user, 15, 'Support correction');

        $this->assertSame('adjustment', $tx->type);
        $this->assertSame(15, $tx->balance_after);
    }
}
