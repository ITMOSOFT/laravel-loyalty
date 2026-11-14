<?php
namespace ItmoSoft\Loyalty\Tests\Support;
use Illuminate\Database\Eloquent\Model;
use ItmoSoft\Loyalty\Concerns\HasLoyaltyAccount;
class User extends Model { use HasLoyaltyAccount; protected $table='users'; protected $guarded=[]; }
