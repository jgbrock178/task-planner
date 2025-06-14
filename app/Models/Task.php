<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'completed_at',
        'sort_order',
        'priority',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'is_completed',
        'completed_ago',
    ];

    public const PRIORITY_ORDER = [
        'high',
        'medium',
        'low',
        'none',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include tasks that are completed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope a query to only include tasks that are not completed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCompleted($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope a query to order by priority.
     *
     * Usage: Task::orderByPriority()->get();
     *        Task::orderByPriority('desc')->get();
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction  'asc' or 'desc'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPriority(Builder $query, string $direction = 'asc')
    {
        $dir = strcasecmp($direction, 'desc') === 0 ? 'DESC' : 'ASC';

        $case = <<<'SQL'
        CASE priority
            WHEN 'high'   THEN 1
            WHEN 'medium' THEN 2
            WHEN 'low'    THEN 3
            ELSE 4
        END
        SQL;

        return $query->orderByRaw("$case $dir");
    }

    /**
     * Adds an accessor to determine if the task is completed, based on the completed_at field.
     *
     * @return bool
     */
    public function getIsCompletedAttribute()
    {
        return ! is_null($this->completed_at);
    }

    /**
     * Adds an accessor to get the time since the task was completed in a human-readable format.
     *
     * @return string|null
     */
    public function getCompletedAgoAttribute()
    {
        return $this->completed_at ? $this->completed_at->diffForHumans() : null;
    }

    /**
     * Marks the task as completed by setting the completed_at timestamp.
     */
    public function complete()
    {
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Unmarks the task as completed by setting the completed_at timestamp to null.
     */
    public function incomplete()
    {
        $this->completed_at = null;
        $this->save();
    }
}
