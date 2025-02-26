<?php

namespace YiddisheKop\LaravelCommerce\Traits;

use YiddisheKop\LaravelCommerce\Models\Coupon;
use YiddisheKop\LaravelCommerce\Contracts\Order;
use YiddisheKop\LaravelCommerce\Exceptions\CouponExpired;
use YiddisheKop\LaravelCommerce\Exceptions\CouponLimitReached;

/**
 * Coupon methods
 */
trait HandlesCoupons
{

    /**
     * Check if the coupon is valid
     */
    public function isValid()
    {
        if (($this->valid_from && $this->valid_from > now())
            || ($this->valid_to && $this->valid_to < now())
        ) {
            return false;
        }
        return true;
    }

    /**
     * Apply the coupon to an Order
     */
    public function apply(Order $order)
    {
        if (!$this->isValid()) {
            throw new CouponExpired("The coupon is no longer valid", 1);
        }
        if (!is_null($this->max_uses) && $this->times_used >= $this->max_uses) {
            throw new CouponLimitReached("The coupon has been used to it's max", 1);
        }
        $order->update([
            'coupon_id' => $this->id
        ]);
        return true;
    }

    /**
     * Calculate the amount to discount the Order
     */
    public function calculateDiscount($originalPrice)
    {
        if (!$this->isValid()) {
            return 0;
        }
        if (!is_null($this->max_uses) && $this->times_used >= $this->max_uses) {
            return 0;
        }
        if ($this->type == Coupon::TYPE_FIXED) {
            $discount = $this->discount;
        } else if ($this->type == Coupon::TYPE_PERCENTAGE) {
            $discount = ($originalPrice / 100) * $this->discount;
        }
        return $discount;
    }
}
