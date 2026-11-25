<?php

namespace ItmoSoft\Loyalty\Tests\Unit;

use ItmoSoft\Loyalty\LoyaltyManager;
use ItmoSoft\Loyalty\Tests\Support\User;
use ItmoSoft\Loyalty\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;

class LoyaltyManagerTest extends TestCase
{
    public function test_it_earns_points_and_tracks_the_balance(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $tx = app(LoyaltyManager::class)->earn($user, 100, 'Order completed', 'ORDER-100');

        $this->assertSame(100, $tx->points);
        $this->assertSame(100, $tx->balance_after);
        $this->assertSame(100, (int) $user->loyaltyAccount()->first()->balance);
        $this->assertSame('earn', $tx->type);
    }

    public function test_it_rejects_zero_or_negative_earned_points(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $this->expectException(InvalidArgumentException::class);

        app(LoyaltyManager::class)->earn($user, 0, 'Invalid award');
    }

    public function test_it_prevents_redemption_beyond_available_balance(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $manager = app(LoyaltyManager::class);
        $manager->earn($user, 20, 'Welcome bonus');

        $this->expectException(RuntimeException::class);
        $manager->redeem($user, 25, 'Order discount');
    }

    public function test_it_rejects_an_unsaved_customer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(LoyaltyManager::class)->earn(new User(['name' => 'Unsaved']), 10, 'Welcome');
    }

    public function test_it_rejects_values_outside_the_transaction_column_range(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $this->expectException(InvalidArgumentException::class);

        app(LoyaltyManager::class)->earn($user, 2147483648, 'Too many points');
    }

    public function test_it_rejects_overlong_reason_and_reference(): void
    {
        $user = User::create(['name' => 'Adeel']);

        try {
            app(LoyaltyManager::class)->earn($user, 1, str_repeat('r', 192));
            $this->fail('An overlong reason should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('reason', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        app(LoyaltyManager::class)->earn($user, 1, 'Valid reason', str_repeat('x', 192));
    }

    public function test_it_rejects_a_balance_that_would_overflow_php_integer_range(): void
    {
        $user = User::create(['name' => 'Adeel']);
        $account = $user->getOrCreateLoyaltyAccount();
        $account->forceFill(['balance' => PHP_INT_MAX])->save();

        $this->expectException(InvalidArgumentException::class);
        app(LoyaltyManager::class)->earn($user, 1, 'Overflow');
    }

    public function test_it_applies_configured_expiry_to_new_earnings(): void
    {
        config(['itmosoft-loyalty.points_expire_after_days' => 30]);
        $user = User::create(['name' => 'Adeel']);

        $tx = app(LoyaltyManager::class)->earn($user, 10, 'Welcome');

        $this->assertNotNull($tx->expires_at);
        $this->assertTrue($tx->expires_at->isFuture());
    }

    public function test_it_allows_negative_balances_when_configured(): void
    {
        config(['itmosoft-loyalty.allow_negative_balance' => true]);
        $user = User::create(['name' => 'Adeel']);

        $tx = app(LoyaltyManager::class)->redeem($user, 10, 'Allowed overdraft');

        $this->assertSame(-10, $tx->balance_after);
        $this->assertSame(-10, (int) $user->loyaltyAccount()->first()->balance);
    }
}
