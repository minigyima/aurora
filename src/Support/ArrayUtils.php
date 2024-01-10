<?php

namespace Minigyima\Aurora\Support;


/**
 * ArrayUtils - Utility class for array operations
 */
class ArrayUtils
{
    /**
     * Delete a value from an array if it exists
     * @param array $array
     * @param mixed $value
     * @return array
     */
    public static function deleteIfExists(array $array, mixed $value): array
    {
        return (array) self::exists($array, $value) ? self::delete($array, $value) : $array;
    }

    /**
     * Check if a value exists in an array
     * - Pointless wrapper around in_array()
     * @param array $array
     * @param mixed $value
     * @return bool
     */
    public static function exists(array $array, mixed $value): bool
    {
        return in_array($value, $array);
    }

    /**
     * Delete a value from an array
     * @param array $array
     * @param mixed $value
     * @return array
     */
    public static function delete(array $array, mixed $value): array
    {
        return (array) array_values(array_diff($array, [$value]));
    }

    /**
     * Merge arrays and remove duplicates
     * @param array $array
     * @param array ...$arrays
     * @return array
     */
    public static function mergeUnique(array $array, array ...$arrays): array
    {
        return array_unique([...$array, ...$arrays], SORT_REGULAR);
    }

    /**
     * Push a value to an array if it doesn't exist
     * @param array $array
     * @param mixed $value
     * @return array
     */
    public static function pushUnique(array $array, mixed $value): array
    {
        return array_unique([...$array, $value]);
    }
}
