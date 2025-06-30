<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'points_awarded',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logAction(User $user, Action $action, $status, $description = null): void
    {
        $actionName = $action['name'];
        $actionSlug = $action['slug'];
        $pointsAwarded = $action['points'];

        $log = new self;

        $log->user_id = $user['id'];
        $log->action = $actionSlug;
        $log->points_awarded = $pointsAwarded;
        $log->description = $description ?? "Activity bonus ($actionName)";
        $log->status = $status;

        $log->save();
    }
}
