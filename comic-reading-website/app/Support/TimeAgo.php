<?php

namespace App\Support;

use Carbon\CarbonInterface;

class TimeAgo
{
    /**
     * Thời gian đã trôi qua bằng tiếng Việt: "vừa xong", "5 phút trước",
     * "2 tiếng trước", "3 ngày trước", "2 tuần trước", "4 tháng trước", "1 năm trước".
     */
    public static function vi(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        $seconds = max(0, now()->getTimestamp() - $date->getTimestamp());

        return match (true) {
            $seconds < 60          => 'vừa xong',
            $seconds < 3600        => (int) floor($seconds / 60) . ' phút trước',
            $seconds < 86400       => (int) floor($seconds / 3600) . ' tiếng trước',
            $seconds < 604800      => (int) floor($seconds / 86400) . ' ngày trước',
            $seconds < 2592000     => (int) floor($seconds / 604800) . ' tuần trước',
            $seconds < 31536000    => (int) floor($seconds / 2592000) . ' tháng trước',
            default                => (int) floor($seconds / 31536000) . ' năm trước',
        };
    }
}
