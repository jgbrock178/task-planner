<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'is_completed',
        'completed_ago',
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
