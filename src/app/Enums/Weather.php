<?php

namespace App\Enums;

enum Weather: string
{
    case CLEAR          = 'Clear';          // 晴れ
    case RAIN           = 'Rain';           // 雨
    case SNOW           = 'Snow';           // 雪
    case CLOUDS         = 'Clouds';         // 曇り
    case DRIZZLE        = 'Drizzle';        // 霧雨
    case THUNDER_STORM  = 'Thunderstorm';   // 雷雨

    /**
     * 文字列を返す
     */
    public function getName(): string
    {
        return match($this) {
            self::CLEAR         => "晴れ",
            self::RAIN          => "雨",
            self::SNOW          => "雪",
            self::CLOUDS        => "曇り",
            self::DRIZZLE       => "霧雨",
            self::THUNDER_STORM => "雷雨",
        };
    }
}
