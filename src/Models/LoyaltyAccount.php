<?php
namespace ItmoSoft\Loyalty\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LoyaltyAccount extends Model {
 protected $guarded = [];
 protected $casts = ['balance'=>'integer','metadata'=>'array'];
 public function customer(): MorphTo { return $this->morphTo(); }
 public function transactions(): HasMany { return $this->hasMany(LoyaltyTransaction::class, 'loyalty_account_id')->latest(); }
 public function getTable(): string { return config('itmosoft-loyalty.prefix', '').config('itmosoft-loyalty.accounts_table', 'itmosoft_loyalty_accounts'); }
}
