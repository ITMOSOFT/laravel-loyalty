# Changelog

## [1.0.1] - 2026-11-25
- Changed loyalty account balances and transaction balance snapshots to signed big integers so configured negative balances are supported.
- Rejects unsaved customer models before creating loyalty accounts.
- Validates transaction point values against the database integer range.
- Validates reason and reference lengths before persistence.
- Applies `points_expire_after_days` as the default expiry for earned points when no explicit expiry is supplied.
- Added regression coverage for negative balances, expiry defaults, unsaved customers, and input boundaries.

## [1.0.0] - 2026-11-14
- Initial release.
- Added polymorphic loyalty accounts.
- Added earn, redeem, and adjustment ledger transactions.
- Added balance snapshots and optional expiry metadata.
- Added configurable table names, prefix, and redemption policy.
- Added Laravel 12/13 CI matrix.
