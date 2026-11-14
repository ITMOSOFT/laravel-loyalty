<?php
namespace ItmoSoft\Loyalty;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ItmoSoft\Loyalty\Models\LoyaltyAccount;
use ItmoSoft\Loyalty\Models\LoyaltyTransaction;
use RuntimeException;
class LoyaltyManager {
 public function __construct(private readonly array $config = []) {}
 public function accountFor(Model $customer): LoyaltyAccount {
  if (!method_exists($customer, 'getOrCreateLoyaltyAccount')) throw new InvalidArgumentException('The customer model must use the HasLoyaltyAccount trait.');
  return $customer->getOrCreateLoyaltyAccount();
 }
 public function earn(Model $customer, int $points, string $reason, ?string $reference=null, ?DateTimeInterface $expiresAt=null, array $metadata=[]): LoyaltyTransaction {
  if ($points <= 0) throw new InvalidArgumentException('Earned points must be greater than zero.');
  return $this->record($customer,$points,'earn',$reason,$reference,$expiresAt,$metadata);
 }
 public function redeem(Model $customer, int $points, string $reason, ?string $reference=null, array $metadata=[]): LoyaltyTransaction {
  if ($points <= 0) throw new InvalidArgumentException('Redeemed points must be greater than zero.');
  $minimum=(int)($this->config['minimum_redemption']??1);
  if ($points < $minimum) throw new InvalidArgumentException("Redemption must be at least {$minimum} points.");
  return $this->record($customer,-$points,'redeem',$reason,$reference,null,$metadata);
 }
 public function adjust(Model $customer, int $points, string $reason, ?string $reference=null, array $metadata=[]): LoyaltyTransaction {
  if ($points===0) throw new InvalidArgumentException('Adjustment points cannot be zero.');
  return $this->record($customer,$points,'adjustment',$reason,$reference,null,$metadata);
 }
 private function record(Model $customer,int $points,string $type,string $reason,?string $reference,?DateTimeInterface $expiresAt,array $metadata): LoyaltyTransaction {
  if (trim($reason)==='') throw new InvalidArgumentException('A transaction reason is required.');
  return DB::transaction(function() use($customer,$points,$type,$reason,$reference,$expiresAt,$metadata) {
   $account=$this->accountFor($customer);
   $account=LoyaltyAccount::query()->whereKey($account->getKey())->lockForUpdate()->firstOrFail();
   $balance=(int)$account->balance+$points;
   if (!($this->config['allow_negative_balance']??false) && $balance<0) throw new RuntimeException('Insufficient loyalty points.');
   $account->forceFill(['balance'=>$balance])->save();
   return $account->transactions()->create(['type'=>$type,'points'=>$points,'balance_after'=>$balance,'reason'=>$reason,'reference'=>$reference,'expires_at'=>$expiresAt,'metadata'=>$metadata]);
  });
 }
}
