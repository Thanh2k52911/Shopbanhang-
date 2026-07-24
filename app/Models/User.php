<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
'blocked_at',
'blocked_reason',
'last_login_at',
'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function savedCoupons(): HasMany
{
    return $this->hasMany(
        SavedCoupon::class,
        'user_id'
    );
}


public function productQuestionAnswers(): HasMany
{
    return $this->hasMany(
        ProductQuestionAnswer::class,
        'user_id'
    );
}
    public function favorites(): HasMany
{
    return $this->hasMany(
        ProductFavorite::class,
        'user_id'
    );
}
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
'last_login_at' => 'datetime',
'deleted_at' => 'datetime',
        ];
    }

    public function statusHistories(): HasMany
{
    return $this->hasMany(
        UserStatusHistory::class,
        'user_id'
    );
}

public function createdUserStatusHistories(): HasMany
{
    return $this->hasMany(
        UserStatusHistory::class,
        'created_by'
    );
}
public function isActive(): bool
{
    return $this->status === 'active';
}

public function isBlocked(): bool
{
    return $this->status === 'blocked';
}

public function isInactive(): bool
{
    return $this->status === 'inactive';
}
    /**
     * Các vai trò của người dùng.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    public function createdBanners(): HasMany
{
    return $this->hasMany(
        Banner::class,
        'created_by'
    );
}

public function updatedBanners(): HasMany
{
    return $this->hasMany(
        Banner::class,
        'updated_by'
    );
}

public function createdPages(): HasMany
{
    return $this->hasMany(
        Page::class,
        'created_by'
    );
}

public function updatedPages(): HasMany
{
    return $this->hasMany(
        Page::class,
        'updated_by'
    );
}
    /**
     * Các địa chỉ giao hàng của người dùng.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Địa chỉ mặc định.
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)
            ->where('is_default', true);
    }

    /**
     * Các sản phẩm người dùng yêu thích.
     */
    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_favorites',
            'user_id',
            'product_id'
        )->withTimestamps();
    }

    /**
     * Các đánh giá của người dùng.
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Các phản hồi đánh giá của người dùng.
     */
    public function reviewReplies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Các lượt thích đánh giá.
     */
    public function reviewLikes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * Các đơn hàng.
     */
    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Các giỏ hàng của người dùng.
     */
    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Giỏ hàng đang hoạt động.
     */
    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    /**
     * Tài khoản điểm thưởng.
     */
    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(LoyaltyAccount::class);
    }
    public function createdLoyaltyTransactions(): HasMany
    {
    return $this->hasMany(
        LoyaltyTransaction::class,
        'created_by'
    );
    }
    /**
     * Kiểm tra người dùng có một vai trò hay không.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('roles.name', $roleName)
            ->exists();
    }

    /**
     * Kiểm tra người dùng có ít nhất một vai trò.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()
            ->whereIn('roles.name', $roleNames)
            ->exists();
    }

    /**
     * Kiểm tra quyền thông qua các vai trò.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('permissions.name', $permissionName);
            })
            ->exists();
    }

    /**
     * Kiểm tra tài khoản Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    public function orders(): HasMany
    {
    return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
    return $this->hasMany(Cart::class);
    }

    public function inventoryTransactions(): HasMany
    {
    return $this->hasMany(
        InventoryTransaction::class,
        'created_by'
    );
}

    public function availableCoupons(): BelongsToMany
{
    return $this->belongsToMany(
        Coupon::class,
        'coupon_users',
        'user_id',
        'coupon_id'
    );
}

public function couponUsages(): HasMany
{
    return $this->hasMany(CouponUsage::class);
}

public function createdCoupons(): HasMany
{
    return $this->hasMany(
        Coupon::class,
        'created_by'
    );
}

public function likedReviews(): BelongsToMany
{
    return $this->belongsToMany(
        ProductReview::class,
        'review_likes',
        'user_id',
        'review_id'
    )->withTimestamps();
}

public function createdShipmentHistories(): HasMany
{
    return $this->hasMany(
        ShipmentStatusHistory::class,
        'created_by'
    );
}

public function returnRequests(): HasMany
{
    return $this->hasMany(ReturnRequest::class);
}

public function processedReturnRequests(): HasMany
{
    return $this->hasMany(
        ReturnRequest::class,
        'processed_by'
    );
}

public function processedRefunds(): HasMany
{
    return $this->hasMany(
        Refund::class,
        'processed_by'
    );
}

public function uploadedReturnImages(): HasMany
{
    return $this->hasMany(
        ReturnRequestImage::class,
        'uploaded_by'
    );
}

public function createdReturnStatusHistories(): HasMany
{
    return $this->hasMany(
        ReturnStatusHistory::class,
        'created_by'
    );
}

public function recentlyViewedProducts(): HasMany
{
    return $this->hasMany(RecentlyViewedProduct::class);
}

public function searchHistories(): HasMany
{
    return $this->hasMany(SearchHistory::class);
}

public function productQuestions(): HasMany
{
    return $this->hasMany(ProductQuestion::class);
}



public function contactMessages(): HasMany
{
    return $this->hasMany(ContactMessage::class);
}

public function assignedContactMessages(): HasMany
{
    return $this->hasMany(
        ContactMessage::class,
        'assigned_to'
    );
}

public function newsletterSubscriptions(): HasMany
{
    return $this->hasMany(NewsletterSubscriber::class);
}

public function auditLogs(): HasMany
{
    return $this->hasMany(AuditLog::class);
}

public function loginHistories(): HasMany
{
    return $this->hasMany(LoginHistory::class);
}
}
