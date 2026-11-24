<?php

namespace App\Enums;

enum StudentDataSource: string
{
    case Api = 'api';
    case Manual = 'manual';
    case ImportExcel = 'import_excel';
    case Legacy = 'legacy';

    public function label(): string
    {
        return match ($this) {
            self::Api => 'Sinkron API',
            self::Manual => 'Input Manual',
            self::ImportExcel => 'Import Excel',
            self::Legacy => 'Data Lama',
        };
    }
}
