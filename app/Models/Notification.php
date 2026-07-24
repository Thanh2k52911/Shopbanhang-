<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'category',
        'action_url',
        'image',
        'priority',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Model nhận thông báo.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Chỉ lấy thông báo chưa đọc.
     */
    public function scopeUnread(
        Builder $query
    ): Builder {
        return $query->whereNull('read_at');
    }

    /**
     * Chỉ lấy thông báo đã đọc.
     */
    public function scopeRead(
        Builder $query
    ): Builder {
        return $query->whereNotNull('read_at');
    }

    /**
     * Lọc theo danh mục thông báo.
     */
    public function scopeCategory(
        Builder $query,
        ?string $category
    ): Builder {
        if (! $category) {
            return $query;
        }

        return $query->where(
            'category',
            $category
        );
    }

    /**
     * Lọc theo độ ưu tiên.
     */
    public function scopePriority(
        Builder $query,
        ?string $priority
    ): Builder {
        if (! $priority) {
            return $query;
        }

        return $query->where(
            'priority',
            $priority
        );
    }

    /**
     * Đánh dấu thông báo đã đọc.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at !== null) {
            return true;
        }

        $this->read_at = now();

        return $this->save();
    }

    /**
     * Đánh dấu thông báo chưa đọc.
     */
    public function markAsUnread(): bool
    {
        if ($this->read_at === null) {
            return true;
        }

        $this->read_at = null;

        return $this->save();
    }

    /**
     * Kiểm tra thông báo đã đọc.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Kiểm tra thông báo chưa đọc.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Trả về nhãn tiếng Việt của độ ưu tiên.
     */
    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'low' => 'Thấp',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
            default => 'Bình thường',
        };
    }

    /**
     * Trả về nhãn tiếng Việt của danh mục.
     */
    public function categoryLabel(): string
    {
        return match ($this->category) {
            'order' => 'Đơn hàng',
            'payment' => 'Thanh toán',
            'shipping' => 'Vận chuyển',
            'promotion' => 'Khuyến mãi',
            'review' => 'Đánh giá',
            'question' => 'Hỏi đáp',
            'inventory' => 'Tồn kho',
            'return' => 'Đổi trả',
            'loyalty' => 'Loyalty',
            'contact' => 'Liên hệ',
            default => 'Hệ thống',
        };
    }
}
