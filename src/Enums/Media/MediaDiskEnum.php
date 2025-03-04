<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Enums\Media;

use AbdullahMateen\LaravelHelpingMaterial\Interfaces\ColorsInterface;
use AbdullahMateen\LaravelHelpingMaterial\Traits\General\Enum\GeneralTrait;

enum MediaDiskEnum: int implements ColorsInterface
{
    use GeneralTrait;

    case Temp = 1;
    case Project = 2;
    case Placeholders = 3;
    case Local = 4;
    case Public = 5;

    /**
     * @param string $name
     *
     * @return MediaDiskEnum
     */
    public static function fromName(string $name): MediaDiskEnum
    {
        return match (strtolower($name)) {
            'temp'         => self::Temp,
            'project'      => self::Project,
            'placeholders' => self::Placeholders,
            'local'        => self::Local,
            'public'       => self::Public,
        };
    }

    /**
     * @return string
     */
    public function disk(): string
    {
        return strtolower($this->name);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
        // return match ($this) {
        //     self::Temp         => 'Temp',
        //     self::Project      => 'Project',
        //     self::Placeholders => 'Placeholders',
        //     self::Archive      => 'Archive',
        // };
    }

}
