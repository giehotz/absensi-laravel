<?php

namespace App\Enums;

enum SupervisiStatus: string
{
    case Terjadwal = 'terjadwal';
    case PraObservasi = 'pra_observasi';
    case Observasi = 'observasi';
    case PascaObservasi = 'pasca_observasi';
    case Selesai = 'selesai';
    case Batal = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::Terjadwal => 'Terjadwal',
            self::PraObservasi => 'Pra-Observasi (Form A)',
            self::Observasi => 'Observasi Kelas (Form B)',
            self::PascaObservasi => 'Pasca-Observasi (Form C & D)',
            self::Selesai => 'Selesai',
            self::Batal => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Terjadwal => 'bg-sky-100 text-sky-950 border-black',
            self::PraObservasi => 'bg-amber-100 text-amber-950 border-black',
            self::Observasi => 'bg-violet-100 text-violet-950 border-black',
            self::PascaObservasi => 'bg-orange-100 text-orange-950 border-black',
            self::Selesai => 'bg-emerald-200 text-emerald-950 border-black',
            self::Batal => 'bg-rose-200 text-rose-950 border-black',
        };
    }

    public function isValidTransition(self $next, bool $isKurmer = true): bool
    {
        if ($this === $next) {
            return true;
        }

        if ($next === self::Batal && $this !== self::Selesai) {
            return true;
        }

        if ($isKurmer) {
            return match ($this) {
                self::Terjadwal => $next === self::PraObservasi,
                self::PraObservasi => $next === self::Observasi,
                self::Observasi => $next === self::PascaObservasi,
                self::PascaObservasi => $next === self::Selesai,
                self::Selesai, self::Batal => false,
            };
        }

        return match ($this) {
            self::Terjadwal => in_array($next, [self::Observasi, self::Selesai], true),
            self::Observasi => $next === self::Selesai,
            self::PraObservasi => in_array($next, [self::Observasi, self::Selesai], true),
            self::PascaObservasi => $next === self::Selesai,
            self::Selesai, self::Batal => false,
        };
    }

    public function canAccessObservasi(bool $isKurmer = true): bool
    {
        if (! $isKurmer) {
            return $this !== self::Batal;
        }

        return in_array($this, [self::Observasi, self::PascaObservasi, self::Selesai], true);
    }

    public function canAccessPascaObservasi(bool $isKurmer = true): bool
    {
        if (! $isKurmer) {
            return $this !== self::Batal;
        }

        return in_array($this, [self::PascaObservasi, self::Selesai], true);
    }
}
