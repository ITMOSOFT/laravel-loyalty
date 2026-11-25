# ITMOSOFT Laravel Loyalty

A database-backed loyalty points ledger for Laravel 12 and 13.

## Features
- Polymorphic loyalty accounts for Eloquent customer models.
- Earn, redeem, and manual adjustment transactions.
- Balance updates inside database transactions with row-level locking.
- Transaction history with balance-after snapshots.
- Optional per-earning expiry timestamp, including configurable default expiry days.
- Prevents negative balances by default; signed balance columns support opt-in negative balances.
- Configurable table names, prefix, and minimum redemption.

## Requirements
- PHP 8.3+
- Laravel 12 or 13

## Installation
```bash
composer require itmosoft/laravel-loyalty
php artisan vendor:publish --tag=itmosoft-loyalty-config
php artisan migrate
```
Publishing config is optional. When upgrading from v1.0.0, run migrations so the balance columns become signed before enabling `allow_negative_balance`.

## Usage
Add the trait to your persisted customer model:
```php
use ItmoSoft\Loyalty\Concerns\HasLoyaltyAccount;

class User extends Authenticatable
{
    use HasLoyaltyAccount;
}
```

Earn:
```php
$transaction = app(\ItmoSoft\Loyalty\LoyaltyManager::class)->earn(
    customer: $user,
    points: 100,
    reason: 'Order completed',
    reference: 'ORDER-1001',
    metadata: ['order_id' => 1001],
);
```

Redeem:
```php
app(\ItmoSoft\Loyalty\LoyaltyManager::class)->redeem(
    customer: $user, points: 25, reason: 'Checkout redemption'
);
```

Adjust:
```php
app(\ItmoSoft\Loyalty\LoyaltyManager::class)->adjust(
    customer: $user, points: 10, reason: 'Support correction'
);
```

Read balance and ledger:
```php
$account = $user->getOrCreateLoyaltyAccount();
$balance = $user->loyaltyBalance();
$transactions = $account->transactions;
```

## Configuration
```php
return [
    'accounts_table' => 'itmosoft_loyalty_accounts',
    'transactions_table' => 'itmosoft_loyalty_transactions',
    'prefix' => '',
    'default_currency' => 'points',
    'allow_negative_balance' => false,
    'minimum_redemption' => 1,
    'points_expire_after_days' => null,
];
```

When `points_expire_after_days` is a positive integer, `earn()` assigns an expiry timestamp if the caller does not supply one. Expiry is recorded as ledger metadata only; expired points are not automatically removed from the aggregate balance or excluded from redemption.

## Scope and accounting limitations
- Points are integer units; fractional points are unsupported.
- Expiry timestamps are recorded but the package does not automatically expire points or calculate spendable lots.
- Redemption deducts from the aggregate account balance; FIFO/FEFO lot consumption is not implemented.
- `reference` is informational and is not unique/idempotent. Enforce idempotency in the consuming application.
- `lockForUpdate()` relies on database engine support; SQLite does not provide production-equivalent row-locking guarantees.
- No monetary conversion, tax, discount, eligibility, or external reward fulfillment is included.
- The application is responsible for authorization, tenant scoping, and audit policy.
- This package alone is not a complete financial accounting system.

## Testing
```bash
composer validate --strict
composer install
composer test
```

MIT License. See LICENSE.
