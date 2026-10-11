<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(function ($model) {
            $userId = Auth::id() ?? session('user_id');
            if ($userId && empty($model->created_by)) {
                $model->created_by = $userId;
            }
            if ($userId && empty($model->updated_by)) {
                $model->updated_by = $userId;
            }
        });

        static::updating(function ($model) {
            $userId = Auth::id() ?? session('user_id');
            if ($userId) {
                $model->updated_by = $userId;
            }
        });

        static::deleting(function ($model) {
            $userId = Auth::id() ?? session('user_id');
            if ($userId && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->deleted_by = $userId;
                $model->saveQuietly();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }
}
