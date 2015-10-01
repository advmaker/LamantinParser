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

    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string  $value
     * @param  string  $key
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = self::dataGet($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $itemKey = self::dataGet($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (!isset($target[$segment])) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return value($default);
                }

                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}
