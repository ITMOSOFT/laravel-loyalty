<?php
namespace ItmoSoft\Loyalty\Tests\Unit;
use ItmoSoft\Loyalty\LoyaltyManager;
use ItmoSoft\Loyalty\Tests\Support\User;
use ItmoSoft\Loyalty\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;
class LoyaltyManagerTest extends TestCase {
 public function test_it_earns_points_and_tracks_the_balance(): void {
  $user=User::create(['name'=>'Adeel']); $tx=app(LoyaltyManager::class)->earn($user,100,'Order completed','ORDER-100');
  $this->assertSame(100,$tx->points); $this->assertSame(100,$tx->balance_after);
  $this->assertSame(100,(int)$user->loyaltyAccount()->first()->balance); $this->assertSame('earn',$tx->type);
 }
 public function test_it_rejects_zero_or_negative_earned_points(): void {
  $user=User::create(['name'=>'Adeel']); $this->expectException(InvalidArgumentException::class);
  app(LoyaltyManager::class)->earn($user,0,'Invalid award');
 }
 public function test_it_prevents_redemption_beyond_available_balance(): void {
  $user=User::create(['name'=>'Adeel']); $manager=app(LoyaltyManager::class); $manager->earn($user,20,'Welcome bonus');
  $this->expectException(RuntimeException::class); $manager->redeem($user,25,'Order discount');
 }
}
