<?php

namespace Amsgames\LaravelShop\Contracts;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

interface ShopOrderInterface
{

    /**
     * One-to-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToOne
     */
    public function user();

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items();

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactions();

    /**
     * Returns flag indicating if order is lock and cant be modified by the user.
     * An order is locked the moment it enters pending status.
     *
     * @return bool
     */
    public function getIsLockedAttribute();

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
    public function scopeFindByUser($query, $userId, $statusCode = null);

    /**
     * Returns total amount of items in cart.
     *
     * @return int
     */
    public function getCountAttribute();

    /**
     * Returns total price of all the items in cart.
     *
     * @return float
     */
    public function getTotalPriceAttribute();

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalTaxAttribute();

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalShippingAttribute();

    /**
     * Returns total discount amount based on all coupons applied.
     *
     * @return float
     */
    public function getTotalDiscountAttribute();

    /**
     * Returns total amount to be charged base on total price, tax and discount.
     *
     * @return float
     */
    public function getTotalAttribute();

    /**
     * Returns formatted total price of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalPriceAttribute();

    /**
     * Returns formatted total tax of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalTaxAttribute();

    /**
     * Returns formatted total tax of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalShippingAttribute();

    /**
     * Returns formatted total discount amount based on all coupons applied.
     *
     * @return string
     */
    public function getDisplayTotalDiscountAttribute();

    /**
     * Returns formatted total amount to be charged base on total price, tax and discount.
     *
     * @return string
     */
    public function getDisplayTotalAttribute();

    /**
     * Returns flag indicating if order is in the status specified.
     *
     * @param string $status Status code.
     *
     * @return bool
     */
    public function is($statusCode);

    /**
     * Creates the order's transaction.
     *
     * @param string $gateway       Gateway.
     * @param mixed  $transactionId Transaction ID.
     * @param string $detail        Transaction detail.
     *
     * @return object
     */
    public function placeTransaction($gateway, $transactionId, $detail = '');

    /**
     * Scopes class by item sku.
     * Optionally, scopes by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     * @param mixed                                 $sku    Item SKU.
     *
     * @return this
     */
    public function scopeWhereSKU($query, $sku);

    /**
     * Scopes class by status codes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query       Query.
     * @param array                                 $statusCodes Status.
     *
     * @return this
     */
    public function scopeWhereStatusIn($query, array $statusCodes);

}