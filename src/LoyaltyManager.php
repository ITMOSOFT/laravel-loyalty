<?php

namespace ItmoSoft\Loyalty;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use ItmoSoft\Loyalty\Models\LoyaltyAccount;
use ItmoSoft\Loyalty\Models\LoyaltyTransaction;
use RuntimeException;
use JsonException;

class LoyaltyManager
{
    private const MAX_TRANSACTION_POINTS = 2147483647;
    private const MAX_TEXT_LENGTH = 191;

    public function __construct(private readonly array $config = [])
    {
        $minimum = (int) ($this->config['minimum_redemption'] ?? 1);

        if ($minimum < 1) {
            throw new InvalidArgumentException('Minimum redemption must be at least one point.');
        }
    }

    public function accountFor(Model $customer): LoyaltyAccount
    {
        if (! $customer->exists || $customer->getKey() === null) {
            throw new InvalidArgumentException('The customer must be persisted before using a loyalty account.');
        }

        if (! method_exists($customer, 'getOrCreateLoyaltyAccount')) {
            throw new InvalidArgumentException('The customer model must use the HasLoyaltyAccount trait.');
        }

        return $customer->getOrCreateLoyaltyAccount();
    }

    public function earn(
        Model $customer,
        int $points,
        string $reason,
        ?string $reference = null,
        ?DateTimeInterface $expiresAt = null,
        array $metadata = [],
    ): LoyaltyTransaction {
        $this->validatePoints($points, 'Earned points');

        if ($points <= 0) {
            throw new InvalidArgumentException('Earned points must be greater than zero.');
        }

        if ($expiresAt === null && ($days = $this->config['points_expire_after_days'] ?? null) !== null) {
            if (! is_numeric($days) || (int) $days < 1) {
                throw new InvalidArgumentException('Points expiry days must be null or a positive integer.');
            }

            $expiresAt = Carbon::now()->addDays((int) $days);
        }

        return $this->record($customer, $points, 'earn', $reason, $reference, $expiresAt, $metadata);
    }

    public function redeem(
        Model $customer,
        int $points,
        string $reason,
        ?string $reference = null,
        array $metadata = [],
    ): LoyaltyTransaction {
        $this->validatePoints($points, 'Redeemed points');

        if ($points <= 0) {
            throw new InvalidArgumentException('Redeemed points must be greater than zero.');
        }

        $minimum = (int) ($this->config['minimum_redemption'] ?? 1);

        if ($points < $minimum) {
            throw new InvalidArgumentException("Redemption must be at least {$minimum} points.");
        }

        return $this->record($customer, -$points, 'redeem', $reason, $reference, null, $metadata);
    }

    public function adjust(
        Model $customer,
        int $points,
        string $reason,
        ?string $reference = null,
        array $metadata = [],
    ): LoyaltyTransaction {
        $this->validatePoints($points, 'Adjustment points');

        if ($points === 0) {
            throw new InvalidArgumentException('Adjustment points cannot be zero.');
        }

        return $this->record($customer, $points, 'adjustment', $reason, $reference, null, $metadata);
    }

    private function validatePoints(int $points, string $label): void
    {
        if ($points > self::MAX_TRANSACTION_POINTS || $points < -self::MAX_TRANSACTION_POINTS) {
            throw new InvalidArgumentException("{$label} must fit the signed 32-bit transaction column.");
        }
    }

    private function record(
        Model $customer,
        int $points,
        string $type,
        string $reason,
        ?string $reference,
        ?DateTimeInterface $expiresAt,
        array $metadata,
    ): LoyaltyTransaction {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A transaction reason is required.');
        }

        if (mb_strlen($reason) > self::MAX_TEXT_LENGTH) {
            throw new InvalidArgumentException('Transaction reason cannot exceed 191 characters.');
        }

        if ($reference !== null && mb_strlen($reference) > self::MAX_TEXT_LENGTH) {
            throw new InvalidArgumentException('Transaction reference cannot exceed 191 characters.');
        }

        try {
            json_encode($metadata, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Transaction metadata must be JSON-encodable.', previous: $exception);
        }

        return DB::transaction(function () use ($customer, $points, $type, $reason, $reference, $expiresAt, $metadata) {
            $account = $this->accountFor($customer);
            $account = LoyaltyAccount::query()
                ->whereKey($account->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $currentBalance = (int) $account->balance;

            if (
                ($points > 0 && $currentBalance > PHP_INT_MAX - $points)
                || ($points < 0 && $currentBalance < PHP_INT_MIN - $points)
            ) {
                throw new InvalidArgumentException('The resulting loyalty balance exceeds the supported integer range.');
            }

            $balance = $currentBalance + $points;

            if (! ($this->config['allow_negative_balance'] ?? false) && $balance < 0) {
                throw new RuntimeException('Insufficient loyalty points.');
            }

            $account->forceFill(['balance' => $balance])->save();

            return $account->transactions()->create([
                'type' => $type,
                'points' => $points,
                'balance_after' => $balance,
                'reason' => $reason,
                'reference' => $reference,
                'expires_at' => $expiresAt,
                'metadata' => $metadata,
            ]);
        });
    }
}
