<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'table_name', 'record_id', 'action', 'old_data', 'new_data',
    ];

    protected $casts = [
        'old_data'   => 'array',
        'new_data'   => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(
        string $table,
        int    $recordId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        static::create([
            'table_name' => $table,
            'record_id'  => $recordId,
            'action'     => $action,
            'old_data'   => $oldData,
            'new_data'   => $newData,
        ]);
    }
}