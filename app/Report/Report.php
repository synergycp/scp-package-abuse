<?php

namespace Packages\Abuse\App\Report;

use App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Builder;

/**
 * Database representation of an Abuse Report.
 */
class Report
extends Models\Model
{
    /**
     * @var int
     */
    const PENDING_CLIENT = 0;

    /**
     * @var int
     */
    const PENDING_ADMIN = 1;

    /**
     * @var string
     */
    public static $singular = 'Abuse Report';

    /**
     * @var string
     */
    public static $plural = 'Abuse Reports';

    /**
     * @var string
     */
    public static $controller = 'api.abuse';

    protected $table = 'abuse_reports';

    protected $fillable = [
        'subject', 'body', 'reported_at', 'addr',
    ];

    protected $dates = [
        'reported_at', 'resolved_at',
    ];

    /**
     * @var array
     */
    protected $searchCols = [
        'addr',
        'clients.first', 'clients.last', 'clients.email',
        'servers.srv_id', 'servers.nickname',
    ];

    # Attributes
    /**
     * Get the date that the incident was reported.
     *
     * @return Carbon
     */
    public function getDateAttribute()
    {
        return $this->reported_at;
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->addr;
    }

    /**
     * @return bool
     */
    public function getIsResolvedAttribute()
    {
        return $this->isResolved();
    }

    /**
     * Syntax for ->is_resolved = true || false.
     *
     * @param bool $value
     */
    public function setIsResolvedAttribute($value)
    {
        $method = $value ? 'resolve' : 'unresolve';
        $this->{$method}();
    }

    # Methods
    /**
     * Mark this abuse report as resolved.
     * Does not save the change to the database.
     *
     * @return $this
     */
    public function resolve()
    {
        $this->resolved_at = new Carbon();

        return $this;
    }

    /**
     * Mark this abuse report as no longer resolved.
     * Does not save the change to the database.
     *
     * @return $this
     */
    public function unresolve()
    {
        $this->resolved_at = null;

        return $this;
    }

    /**
     * Set the status to pending admin.
     */
    public function setPendingAdmin()
    {
        $this->pending_type = static::PENDING_ADMIN;

        return $this;
    }

    /**
     * Set the status to pending client.
     */
    public function setPendingClient()
    {
        $this->pending_type = static::PENDING_CLIENT;

        return $this;
    }

    /**
     * Determine whether or not the Abuse Report is resolved.
     *
     * @return bool
     */
    public function isResolved()
    {
        return (bool) $this->resolved_at;
    }

    # Relationships
    /**
     * The Entity that the reported IP Address is in.
     *
     * @return Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Models\Entity::class);
    }

    /**
     * The Server that was using the reported IP Address.
     *
     * @return Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Models\Server::class);
    }

    /**
     * The Client that owned the Entity at the time of the report.
     *
     * @return Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Models\Client::class);
    }

    /**
     * The comments posted on the report.
     *
     * @return Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment\Comment::class, 'abuse_report_id');
    }

    /**
     * The latest comment posted on the report.
     *
     * @return Relations\HasOne
     */
    public function lastComment()
    {
        return $this
            ->hasOne(Comment\Comment::class, 'abuse_report_id')
            ->orderBy('created_at', 'desc')
            ;
    }

    # Scopes
    /**
     * Filter the query by abuse reports that have status archived.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeArchived(Builder $query)
    {
        return $query->whereNotNull($this->table.'.resolved_at');
    }

    /**
     * Filter the query by abuse reports that have status open.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOpen(Builder $query)
    {
        return $query->whereNull($this->table.'.resolved_at');
    }

    /**
     * Filter the query by abuse reports that match the given search string.
     *
     * @param Builder $query
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearch(Builder $query, $search)
    {
        $findWord = function (Builder $query, $word) {
            $searchCol = function (Builder $query, $col) use (&$word) {
                return $query->orWhere($col, 'LIKE', "%$word%");
            };

            $matchAllSearchCols = function (Builder $query) use (&$searchCol) {
                return collect($this->searchCols)->reduce($searchCol, $query);
            };

            return $query->where($matchAllSearchCols);
        };

        $query = $this->joinSearchTables($query);

        return get_search_words($search)->reduce($findWord, $query);
    }

    /**
     * Join the search query with the relevant search tables.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    private function joinSearchTables(Builder $query)
    {
        return $query
            ->select('abuse_reports.*')
            ->leftJoin('entities', 'entities.id', '=', 'abuse_reports.entity_id')
            ->leftJoin('servers', 'servers.id', '=', 'entities.server_id')
            ->leftJoin('clients', 'clients.id', '=', 'abuse_reports.client_id')
            ;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePendingClient(Builder $query)
    {
        return $query
            ->open()
            ->where('pending_type', static::PENDING_CLIENT)
            ;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePendingAdmin(Builder $query)
    {
        return $query
            ->open()
            ->where('pending_type', static::PENDING_ADMIN)
            ;
    }

    /**
     * @param Builder $query
     * @param string  $joinType
     * @param string  $alias
     *
     * @return Builder
     */
    public function scopeJoinServer(
        Builder $query,
        $joinType = 'inner',
        $alias = 'servers'
    ) {
        $query->select('abuse_reports.*')->join(
            "servers as $alias",
            "$alias.id", '=', 'abuse_reports.server_id',
            $joinType
        );
    }
}