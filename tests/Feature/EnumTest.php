<?php declare(strict_types = 1);

namespace Tests\Feature;

use AbdullahMateen\LaravelHelpingMaterial\Enums\StatusEnum;
use AbdullahMateen\LaravelHelpingMaterial\Enums\User\AccountStatusEnum;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function test_status_enum_helpers_return_expected_values(): void
    {
        self::assertSame(
            ['Active' => 1, 'Inactive' => 0],
            StatusEnum::toArray(),
        );
        self::assertTrue(StatusEnum::exists(1));
        self::assertFalse(StatusEnum::exists(null));
        self::assertContains(StatusEnum::random(), [0, 1]);
        self::assertSame('Active', StatusEnum::Active->toString());
        self::assertSame('success', StatusEnum::Active->color());
        self::assertSame('#28a745', StatusEnum::Active->colorCode());

        $fullArray = StatusEnum::toFullArray();

        self::assertSame('Active', $fullArray[1]['string']);
        self::assertSame('success', $fullArray[1]['color']);
        self::assertSame('#28a745', $fullArray[1]['colorCode']);
    }

    public function test_account_status_enum_editable_states_remain_available(): void
    {
        self::assertSame(
            [
                AccountStatusEnum::Active,
                AccountStatusEnum::Inactive,
                AccountStatusEnum::Suspend,
                AccountStatusEnum::Blocked,
            ],
            AccountStatusEnum::editable(),
        );
    }
}
