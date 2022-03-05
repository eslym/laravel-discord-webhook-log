<?php

namespace Eslym\Laravel\Log\DiscordWebhook;

final class Utils
{
    /**
     * Escape discord markdown syntax
     * @param string $string
     * @param string[] $escape
     * @return string
     */
    public static function escapeMarkdown(string $string, array $escape = ['`', '|', '*', '_', '~', '\\', '>']): string
    {
        return join(array_map(function ($char) use ($escape) {
            return in_array($char, $escape) ?
                '\\' . $char : $char;
        }, preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY)));
    }

    /**
     * Limit string to a specific length
     * @param string $string
     * @param int $len
     * @return string
     */
    public static function limitStr(string $string, int $len): string
    {
        if (self::mbStrLen($string) > $len) {
            return self::mbSubStr($string, $len - 3) . '...';
        }
        return $string;
    }

    /**
     * Determine characters length in string
     * @param string $string
     * @return false|int
     */
    public static function mbStrLen(string $string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string);
        }
        return count(preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Trim a string to specific length
     * @param string $string
     * @param $length
     * @return string
     */
    public static function mbSubStr(string $string, $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, 0, $length);
        }
        return join(array_slice(preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY), 0, $length));
    }
}