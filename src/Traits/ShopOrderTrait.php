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
            if (!method_exists(Config::get('auth.providers.users.model'), 'bootSoftDeletingTrait')) {
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
        return $this->belongsTo(Config::get('auth.providers.users.model'), 'user_id');
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
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactions()
    {
        return $this->hasMany(Config::get('shop.transaction'), 'order_id');
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
     * Scopes class by item sku.
     * Optionally, scopes by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     * @param mixed                                 $sku    Item SKU.
     *
     * @return this
     */
    public function scopeWhereSKU($query, $sku) {
        return $query->join(
                config('shop.item_table'), 
                config('shop.item_table') . '.order_id', 
                '=', 
                $this->table . '.id'
            )
            ->where(config('shop.item_table') . '.sku', $sku);
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
     * Scopes class by status codes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query       Query.
     * @param array                                 $statusCodes Status.
     *
     * @return this
     */
    public function scopeWhereStatusIn($query, array $statusCodes) {
        return $query = $query->whereIn('statusCode', $statusCodes);
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

    /**
     * Returns flag indicating if order is completed.
     *
     * @return bool
     */
    public function getIsCompletedAttribute()
    {
        return $this->attributes['statusCode'] == 'completed';
    }

    /**
     * Returns flag indicating if order has failed.
     *
     * @return bool
     */
    public function getHasFailedAttribute()
    {
        return $this->attributes['statusCode'] == 'failed';
    }

    /**
     * Returns flag indicating if order is canceled.
     *
     * @return bool
     */
    public function getIsCanceledAttribute()
    {
        return $this->attributes['statusCode'] == 'canceled';
    }

    /**
     * Returns flag indicating if order is in process.
     *
     * @return bool
     */
    public function getIsInProcessAttribute()
    {
        return $this->attributes['statusCode'] == 'in_process';
    }

    /**
     * Returns flag indicating if order is in creation.
     *
     * @return bool
     */
    public function getIsInCreationAttribute()
    {
        return $this->attributes['statusCode'] == 'in_creation';
    }

    /**
     * Returns flag indicating if order is in creation.
     *
     * @return bool
     */
    public function getIsPendingAttribute()
    {
        return $this->attributes['statusCode'] == 'pending';
    }

    /**
     * Creates the order's transaction.
     *
     * @param string $gateway       Gateway.
     * @param mixed  $transactionId Transaction ID.
     * @param string $detail        Transaction detail.
     *
     * @return object
     */
    public function placeTransaction($gateway, $transactionId, $detail = null, $token = null)
    {
        return call_user_func(Config::get('shop.transaction') . '::create', [
            'order_id'          => $this->attributes['id'],
            'gateway'           => $gateway,
            'transaction_id'    => $transactionId,
            'detail'            => $detail,
            'token'             => $token,
        ]);
    }

    /**
     * Retrieves item from order;
     *
     * @param string $sku SKU of item.
     *
     * @return mixed
     */
    private function getItem($sku)
    {
        $className  = Config::get('shop.item');
        $item       = new $className();
        return $item->where('sku', $sku)
            ->where('order_id', $this->attributes['id'])
            ->first();
    }
}