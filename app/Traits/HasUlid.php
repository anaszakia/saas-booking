<?php

namespace App\Traits;

use Symfony\Component\Uid\Ulid;

trait HasUlid
{
    public static function bootHasUlid(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) new Ulid();
            }
        });
    }

    /**
     * Jadikan kolom 'ulid' sebagai route key default untuk Route Model Binding
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
