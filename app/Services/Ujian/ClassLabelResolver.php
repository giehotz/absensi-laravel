<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use Illuminate\Support\Collection;

class ClassLabelResolver
{
    /**
     * Ekstraksi tingkat angka dari nama kelas (mis. "Kelas 1 a" -> "1").
     */
    public static function levelFromName(string $name): ?string
    {
        if (preg_match('/(\d+)/', $name, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Konversi nama kelas menjadi label ringkas template (mis. "Kelas 1 a" -> "1A").
     */
    public static function displayLabel(string $name): string
    {
        if (preg_match('/(?:kelas\s*)?(\d+)\s*([a-zA-Z])?/i', trim($name), $matches)) {
            $level = $matches[1];
            $char = isset($matches[2]) ? strtoupper($matches[2]) : '';

            return $level.$char;
        }

        return trim($name);
    }

    /**
     * Normalisasi nama kelas atau label untuk pencocokan toleran.
     */
    public static function normalize(string $name): string
    {
        $normalized = mb_strtolower(trim($name));
        $normalized = str_replace(['kelas', '.', '-', '_', ' '], '', $normalized);

        return $normalized;
    }

    /**
     * Mencari model SchoolClass berdasarkan label/nama kelas.
     *
     * @param  Collection<int, SchoolClass>|null  $classes
     */
    public static function resolveClassByLabel(string $label, ?Collection $classes = null): ?SchoolClass
    {
        $targetNormalized = self::normalize($label);
        if ($targetNormalized === '') {
            return null;
        }

        $classes = $classes ?? SchoolClass::all();

        // 1. Coba pencocokan eksak pada nama asli
        $exact = $classes->first(fn ($c) => mb_strtolower(trim($c->name)) === mb_strtolower(trim($label)));
        if ($exact) {
            return $exact;
        }

        // 2. Coba pencocokan normalisasi
        $matched = $classes->first(fn ($c) => self::normalize($c->name) === $targetNormalized);
        if ($matched) {
            return $matched;
        }

        // 3. Coba displayLabel cocok
        return $classes->first(fn ($c) => mb_strtolower(self::displayLabel($c->name)) === mb_strtolower(trim($label)));
    }
}
