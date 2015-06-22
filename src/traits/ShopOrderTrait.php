<?php

namespace Amsgames\LaravelShop\Traits;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait ShopOrderTrait
{

    /**
     * Boot the user model
     * Attach event listener to remove the relationship records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function($user) {
            if (!method_exists(Config::get('auth.model'), 'bootSoftDeletingTrait')) {
                $user->items()->sync([]);
            }

            return true;
        });
    }

    /**
     * One-to-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsTo(Config::get('auth.model'), 'user_id');
    }

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->hasMany(Config::get('shop.item'), 'order_id');
    }

    /**
     * Returns flag indicating if order is lock and cant be modified by the user.
     * An order is locked the moment it enters pending status.
     *
     * @return bool
     */
    public function getIsLockedAttribute() {
        return in_array($this->attributes['statusCode'], Config::get('shop.order_status_lock'));
    }

    /**
     * Scopes class by user ID and returns object.
     * Optionally, scopes by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     * @param mixed                                 $userId User ID.
     *
     * @return this
     */
    public function scopeWhereUser($query, $userId) {
        return $query->where('user_id', $userId);
    }

    /**
     * Scopes class by user ID and returns object.
     * Optionally, scopes by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query      Query.
     * @param string                                $statusCode Status.
     *
     * @return this
     */
    public function scopeWhereStatus($query, $statusCode) {
        return $query = $query->where('statusCode', $statusCode);
    }

    /**
     * Scopes class by user ID and returns object.
     * Optionally, scopes by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query      Query.
     * @param mixed                                 $userId     User ID.
     * @param string                                $statusCode Status.
     *
     * @return this
     */
    public function scopeFindByUser($query, $userId, $statusCode = null) {
        if (!empty($status)) {
            $query = $query->whereStatus($status);
        }
        return $query->whereUser($userId)->get();
    }

    /**
     * Returns flag indicating if order is in the status specified.
     *
     * @param string $status Status code.
     *
     * @return bool
     */
    public function is($statusCode)
    {
        return $this->attributes['statusCode'] == $statusCode;
    }

}