<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\User;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum TitleEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Mr = 1;
    case Mrs = 2;
    case Mister = 3;
    case Miss = 4;
    case Ms = 5;
    case Mx = 6;

    case Dr = 7;
    case Prof = 8;
    case Rev = 9;
    case Hon = 10;
    case Sir = 11;
    case Dame = 12;

    case Col = 13;
    case Maj = 14;
    case Capt = 15;
    case Sgt = 16;
    case Lt = 17;

    case Lord = 18;
    case Lady = 19;
    case Duke = 20;
    case Duchess = 21;
    case DukeDuchess = 22;
    case Prince = 23;
    case Princess = 24;
    case PrincePrincess = 25;

    case Father = 26;
    case Sister = 27;
    case Imam = 28;
    case Rabbi = 29;

    public static function toFullArrayInclude() { return []; }

    public static function general(): array
    {
        return [
            self::Mr,
            self::Mrs,
            self::Mister,
            self::Miss,
            self::Ms,
            self::Mx,
        ];
    }

    public static function professional(): array
    {
        return [
            self::Dr,
            self::Prof,
            self::Rev,
            self::Hon,
            self::Sir,
            self::Dame,
        ];
    }

    public static function military(): array
    {
        return [
            self::Col,
            self::Maj,
            self::Capt,
            self::Sgt,
            self::Lt,
        ];
    }

    public static function royal(): array
    {
        return [
            self::Lord,
            self::Lady,
            self::Duke,
            self::Duchess,
            self::DukeDuchess,
            self::Prince,
            self::Princess,
            self::PrincePrincess,
        ];
    }

    public static function religious(): array
    {
        return [
            self::Father,
            self::Sister,
            self::Imam,
            self::Rabbi,
        ];
    }

    public function toString()
    {
        return match ($this) {
            self::Mr             => 'Mr.',
            self::Mrs            => 'Mrs.',
            self::Mister         => 'Mr.',
            self::Miss           => 'Ms.',
            self::Ms             => 'Ms.',
            self::Mx             => 'Mx.',
            self::Dr             => 'Dr.',
            self::Prof           => 'Prof.',
            self::Rev            => 'Rev.',
            self::Hon            => 'Hon.',
            self::Sir            => 'Sir.',
            self::Dame           => 'Dame',
            self::Col            => 'Col.',
            self::Maj            => 'Maj.',
            self::Capt           => 'Capt.',
            self::Sgt            => 'Sgt.',
            self::Lt             => 'Lt.',
            self::Lord           => 'Lord',
            self::Lady           => 'Lady',
            self::Duke           => 'Duke',
            self::Duchess        => 'Duchess',
            self::DukeDuchess    => 'Duke/Duchess',
            self::Prince         => 'Prince',
            self::Princess       => 'Princess',
            self::PrincePrincess => 'Prince/Princess',
            self::Father         => 'Father',
            self::Sister         => 'Sister',
            self::Imam           => 'Imam',
            self::Rabbi          => 'Rabbi',
        };
    }

    public function description()
    {
        return match ($this) {
            self::Mr             => 'Mister (used for men, regardless of marital status)',
            self::Mrs            => 'Missus (used for married women)',
            self::Mister         => 'Mister (used for men, regardless of marital status)',
            self::Miss           => '(used for unmarried women)',
            self::Ms             => '(used for women, regardless of marital status)',
            self::Mx             => '(gender-neutral title, used for people who do not identify as male or female)',
            self::Dr             => 'Doctor (used for people with doctoral degrees or medical professionals)',
            self::Prof           => 'Professor (used for academic professionals)',
            self::Rev            => 'Reverend (used for clergy or religious figures)',
            self::Hon            => 'Honorable (used for judges and certain government officials)',
            self::Sir            => '(used for men who have been knighted in the UK)',
            self::Dame           => '(used for women who have been knighted in the UK)',
            self::Col            => 'Colonel',
            self::Maj            => 'Major',
            self::Capt           => 'Captain',
            self::Sgt            => 'Sergeant',
            self::Lt             => 'Lieutenant',
            self::Lord           => '(used for male members of the nobility)',
            self::Lady           => '(used for female members of the nobility)',
            self::Duke           => '(used for high-ranking nobility)',
            self::Duchess        => '(used for high-ranking nobility)',
            self::DukeDuchess    => '(used for high-ranking nobility)',
            self::Prince         => '(used for royalty)',
            self::Princess       => '(used for royalty)',
            self::PrincePrincess => '(used for royalty)',
            self::Father         => '(used for Catholic priests)',
            self::Sister         => '(used for nuns)',
            self::Imam           => '(used for Islamic religious leaders)',
            self::Rabbi          => '(used for Jewish religious leaders)',
        };
    }

    public function color(): string
    {
        return self::PRIMARY_CLASS;
    }

}
