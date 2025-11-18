<?php
/**
 * Helper functions for time formatting
 */

class TimeHelper {
    /**
     * Format thời gian tương đối: <1 ngày => HH:MM; >=1 ngày => tương đối ngày/tháng/năm
     * @param int $timestamp Unix timestamp
     * @return string Formatted time string
     */
    public static function formatRelativeTime($timestamp) {
        $now = time();
        $diff = $now - $timestamp;
        
        if ($diff < 86400) {
            return date('H:i', $timestamp);
        }
        if ($diff < 2 * 86400) {
            return '1 ngày trước';
        }
        if ($diff < 30 * 86400) {
            return floor($diff / 86400) . ' ngày trước';
        }
        if ($diff < 365 * 86400) {
            return floor($diff / (30 * 86400)) . ' tháng trước';
        }
        return floor($diff / (365 * 86400)) . ' năm trước';
    }
}
?>


















