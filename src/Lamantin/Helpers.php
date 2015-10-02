<?php namespace Lamantin;

class Helpers
{
    /**
     * Convert first letter the given string to upper case.
     *
     * @param string $string
     *
     * @return string
     */
    public static function upFirst($string) {
        $length = mb_strlen($string);

        if ($length === 0) {
            return $string;
        }

        if ($length === 1) {
            return mb_strtoupper($string);
        }

        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}
