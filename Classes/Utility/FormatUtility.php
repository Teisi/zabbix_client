<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Utility;

class FormatUtility {

    /**
     * formatBytes
     * automatically sets the appropriate unit
     *
     * @param integer $bytes
     * @param integer $precision
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * formatDateTime
     *
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    public static function formatDateTime(int $timestamp, string $format = 'd M Y H:i:s') {
        $allowedFormat = ['d M Y H:i:s', 'c', 'r'];
        if(in_array($format, $allowedFormat)) {
            $formatTime = $format;

            return date($formatTime, $timestamp);
        }

        return strval($timestamp);
    }
}
