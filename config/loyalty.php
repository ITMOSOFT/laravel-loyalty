<?php
return [
 'accounts_table' => env('ITMOSOFT_LOYALTY_ACCOUNTS_TABLE', 'itmosoft_loyalty_accounts'),
 'transactions_table' => env('ITMOSOFT_LOYALTY_TRANSACTIONS_TABLE', 'itmosoft_loyalty_transactions'),
 'prefix' => env('ITMOSOFT_LOYALTY_TABLE_PREFIX', ''),
 'default_currency' => 'points',
 'allow_negative_balance' => false,
 'minimum_redemption' => 1,
 'points_expire_after_days' => null,
];
