<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database\Eloquent\BaseModel;
use Database\Factories\ForumTopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class ForumTopic extends BaseModel
{
    /** @use HasFactory<ForumTopicFactory> */
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    // TODO rename ForumTopic table to forum_topics
    // TODO rename ID column to id, remove getIdAttribute()
    // TODO rename ForumID to forum_id
    // TODO rename Title to title, remove getTitleAttribute()
    // TODO rename DateCreated to created_at
    // TODO rename Updated to updated_at
    // TODO refactor RequiredPermissions to use RBAC
    // TODO add body from first comment as that's the topic itself
    protected $table = 'ForumTopic';

    protected $primaryKey = 'ID';

    public const CREATED_AT = 'DateCreated';
    public const UPDATED_AT = 'Updated';

    protected $fillable = [
        'ForumID',
        'Title',
        'author_id',
        'LatestCommentID',
        'RequiredPermissions',
    ];

    protected $dispatchesEvents = [
        // 'created' => ForumTopicCreated::class,
    ];

    protected $observables = [];

    // == search

    public function toSearchableArray(): array
    {
        return $this->only([
            'id',
            'Title',
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        // TODO return true;
        return false;
    }

    // == accessors

    // TODO remove after rename
    public function getIdAttribute(): int
    {
        return $this->attributes['ID'];
    }

    public function getCanonicalUrlAttribute(): string
    {
        return route('forum-topic.show', [$this, $this->getSlugAttribute()]);
    }

    public function getPermalinkAttribute(): string
    {
        return route('forum-topic.show', $this);
    }

    public function getEditLinkAttribute(): string
    {
        return route('forum-topic.edit', $this);
    }

    public function getSlugAttribute(): string
    {
        return ($this->forum->category->title ? '-' . Str::slug($this->forum->category->title) : '')
            . ($this->forum->title ? '-' . Str::slug($this->forum->title) : '')
            . ($this->title ? '-' . Str::slug($this->title) : '');
    }

    // TODO remove after rename
    public function getTitleAttribute(): string
    {
        return $this->attributes['Title'];
    }

    // == mutators

    // == relations

    /**
     * @return BelongsTo<User, ForumTopic>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'ID')->withTrashed();
    }

    /**
     * @return BelongsTo<Forum, ForumTopic>
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'ForumID');
    }

    /**
     * @return HasMany<ForumTopicComment>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ForumTopicComment::class, 'ForumTopicID')->with('user');
    }

    /**
     * @return HasOne<ForumTopicComment>
     */
    public function latestComment(): HasOne
    {
        return $this->hasOne(ForumTopicComment::class, 'ForumTopicID')
            ->where('Authorised', 1)
            ->orderByDesc('DateCreated');
    }

    // == scopes

    /**
     * Order by latest comment dynamic relationship
     *
     * @param Builder<ForumTopic> $query
     */
    public function scopeOrderByLatestActivity(Builder $query, string $direction = 'asc'): void
    {
        /*
         * unfortunately there's no easy way to create a coalesce with the query builder to make it work with postgres
         * mysql is ok with ordering coalescing with an alias in it
         * otherwise the above sub-select would've been enough
         */
        $query->selectRaw(
            'coalesce(
            (
                SELECT
                    "created_at"
                FROM
                    "comments"
                WHERE
                    "comments"."commentable_type" = \'forum-topic\'
                    AND "comments"."commentable_id" = "forum_topics"."id"
                    AND "deleted_at" IS NULL
                ORDER BY
                    "created_at" DESC
                LIMIT 1)
                , created_at
            ) as last_activity_at'
        );

        $query->orderByRaw('last_activity_at ' . $direction);
    }

    /**
     * @param Builder<ForumTopic> $query
     * @return Builder<ForumTopic>
     */
    public function scopeWithLatestComment(Builder $query): Builder
    {
        return $query->with(['latestComment.user']);
    }
}
