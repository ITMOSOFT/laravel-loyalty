<?php
namespace ItmoSoft\Loyalty\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LoyaltyTransaction extends Model {
 protected $guarded = [];
 protected $casts = ['points'=>'integer','balance_after'=>'integer','expires_at'=>'datetime','metadata'=>'array'];
 public function account(): BelongsTo { return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id'); }
 public function getTable(): string { return config('itmosoft-loyalty.prefix', '').config('itmosoft-loyalty.transactions_table', 'itmosoft_loyalty_transactions'); }
}
