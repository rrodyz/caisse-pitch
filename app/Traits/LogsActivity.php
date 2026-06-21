<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            static::recordActivity('created', $model, [], $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }
            $old = array_intersect_key($model->getOriginal(), $dirty);
            static::recordActivity('updated', $model, $old, $dirty);
        });

        static::deleted(function ($model) {
            static::recordActivity('deleted', $model, $model->getOriginal(), []);
        });
    }

    protected static function recordActivity(string $action, $model, array $old, array $new): void
    {
        // Skip during seeding / CLI with no authenticated user if desired
        $hidden  = $model->getActivityHiddenAttributes();
        $exclude = array_merge($hidden, ['updated_at', 'created_at', 'remember_token']);
        $filter  = fn(array $arr): array => array_diff_key($arr, array_flip($exclude));

        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => get_class($model),
            'subject_id'   => $model->getKey(),
            'old_values'   => $filter($old) ?: null,
            'new_values'   => $filter($new) ?: null,
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
            'description'  => null,
            'created_at'   => now(),
        ]);
    }

    // Override per model to exclude additional attributes from log
    public function getActivityHiddenAttributes(): array
    {
        return ['password', 'remember_token'];
    }

    // Manual log helper for custom actions (e.g. sale cancelled, discount applied)
    public function logCustomActivity(string $action, string $description = '', array $context = []): void
    {
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => get_class($this),
            'subject_id'   => $this->getKey(),
            'old_values'   => null,
            'new_values'   => $context ?: null,
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
            'description'  => $description,
            'created_at'   => now(),
        ]);
    }
}
