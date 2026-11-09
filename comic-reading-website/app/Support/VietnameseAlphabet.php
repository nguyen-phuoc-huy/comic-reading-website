<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Nhóm danh sách theo chữ cái đầu KHÔNG DẤU, viết hoa, sắp xếp A-Z.
 * Ví dụ: "Ấn độ" và "Âm nhạc" => nhóm A; "Đời thường" => nhóm D; "Ơn trời" => nhóm O.
 * Tên bắt đầu bằng số hoặc ký tự lạ được gom vào nhóm "#" (đặt cuối cùng).
 */
class VietnameseAlphabet
{
    /** Chữ cái nhóm của một chuỗi, ví dụ "Đời thường" => "D", "Âm nhạc" => "A" */
    public static function letterOf(string $text): string
    {
        $text = trim($text);

        // Chuẩn hóa về dạng dựng sẵn (NFC) nếu máy có bật extension intl
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;
        }

        $first = Str::upper(Str::ascii(mb_substr($text, 0, 1)));

        return preg_match('/^[A-Z]$/', $first) ? $first : '#';
    }

    /**
     * Nhóm danh sách theo chữ cái đầu của $field.
     * Trả về Collection dạng: ['A' => [item, item], 'B' => [...], ..., '#' => [...]]
     */
    public static function group(iterable $items, string $field = 'name'): Collection
    {
        return collect($items)
            // Sắp xếp trong từng nhóm theo tên (bỏ dấu để so sánh)
            ->sortBy(fn ($item) => Str::lower(Str::ascii((string) data_get($item, $field))) . '|' . data_get($item, $field))
            ->groupBy(fn ($item) => self::letterOf((string) data_get($item, $field)))
            // Các nhóm theo thứ tự A-Z, nhóm "#" xếp cuối
            ->sortBy(fn ($group, $letter) => $letter === '#' ? 'ZZ' : $letter)
            ->map(fn ($group) => $group->values());
    }
}
