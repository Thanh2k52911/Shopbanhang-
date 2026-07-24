<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ContactMessageController extends Controller
{
    /**
     * Danh sách liên hệ.
     */
    public function index(Request $request): View
    {
        $query = DB::table('contact_messages as cm')
            ->leftJoin('users as u', 'cm.user_id', '=', 'u.id')
            ->leftJoin('orders as o', 'cm.order_id', '=', 'o.id')
            ->leftJoin('users as assignee', 'cm.assigned_to', '=', 'assignee.id')
            ->select([
                'cm.id',
                'cm.contact_code',
                'cm.user_id',
                'cm.name',
                'cm.email',
                'cm.phone',
                'cm.type',
                'cm.subject',
                'cm.message',
                'cm.order_id',
                'cm.status',
                'cm.priority',
                'cm.assigned_to',
                'cm.admin_note',
                'cm.replied_at',
                'cm.closed_at',
                'cm.created_at',
                'cm.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.status as user_status',

                'o.order_code',
                'o.order_status',

                'assignee.name as assignee_name',
                'assignee.email as assignee_email',
            ]);

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'cm.contact_code',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'cm.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'cm.email',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'cm.phone',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'cm.subject',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'cm.message',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'o.order_code',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'assignee.name',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'cm.status',
                $request->input('status')
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'cm.type',
                $request->input('type')
            );
        }

        if ($request->filled('priority')) {
            $query->where(
                'cm.priority',
                $request->input('priority')
            );
        }

        match ($request->input('assignment')) {
            'assigned' => $query->whereNotNull('cm.assigned_to'),
            'unassigned' => $query->whereNull('cm.assigned_to'),
            'mine' => $query->where('cm.assigned_to', auth()->id()),
            default => null,
        };

        match ($request->input('member_type')) {
            'member' => $query->whereNotNull('cm.user_id'),
            'guest' => $query->whereNull('cm.user_id'),
            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('cm.created_at'),
            'priority_desc' => $query
                ->orderByRaw(
                    "CASE cm.priority
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'normal' THEN 3
                        WHEN 'low' THEN 4
                        ELSE 5
                    END"
                )
                ->orderByDesc('cm.created_at'),
            'status' => $query
                ->orderByRaw(
                    "CASE cm.status
                        WHEN 'new' THEN 1
                        WHEN 'processing' THEN 2
                        WHEN 'replied' THEN 3
                        WHEN 'closed' THEN 4
                        WHEN 'spam' THEN 5
                        ELSE 6
                    END"
                )
                ->orderByDesc('cm.created_at'),
            'updated_desc' => $query->orderByDesc('cm.updated_at'),
            default => $query->orderByDesc('cm.created_at'),
        };

        $messages = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = DB::table('contact_messages')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END),
                    0
                ) as new_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END),
                    0
                ) as processing_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END),
                    0
                ) as replied_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END),
                    0
                ) as closed_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END),
                    0
                ) as spam_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(
                        CASE
                            WHEN priority IN ('high', 'urgent')
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) as high_priority_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN assigned_to IS NULL THEN 1 ELSE 0 END),
                    0
                ) as unassigned_count"
            )
            ->first();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'statistics' => $statistics,
            'statuses' => $this->statuses(),
            'types' => $this->types(),
            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Chi tiết liên hệ.
     */
    public function show(int $contactMessage): View
    {
        $message = DB::table('contact_messages as cm')
            ->leftJoin('users as u', 'cm.user_id', '=', 'u.id')
            ->leftJoin('orders as o', 'cm.order_id', '=', 'o.id')
            ->leftJoin('users as assignee', 'cm.assigned_to', '=', 'assignee.id')
            ->where('cm.id', $contactMessage)
            ->select([
                'cm.id',
                'cm.contact_code',
                'cm.user_id',
                'cm.name',
                'cm.email',
                'cm.phone',
                'cm.type',
                'cm.subject',
                'cm.message',
                'cm.order_id',
                'cm.status',
                'cm.priority',
                'cm.assigned_to',
                'cm.admin_note',
                'cm.replied_at',
                'cm.closed_at',
                'cm.created_at',
                'cm.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
                'u.status as user_status',
                'u.last_login_at',
                'u.last_login_ip',

                'o.order_code',
                'o.order_status',
                'o.payment_status',
                'o.shipping_status',
                'o.total_amount',

                'assignee.name as assignee_name',
                'assignee.email as assignee_email',
            ])
            ->first();

        abort_if(! $message, 404);

        $assignees = DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->where('u.status', 'active')
            ->whereExists(
                function (Builder $builder): void {
                    $builder
                        ->selectRaw('1')
                        ->from('user_roles as ur')
                        ->join('roles as r', 'ur.role_id', '=', 'r.id')
                        ->whereColumn('ur.user_id', 'u.id')
                        ->whereIn(
                            'r.name',
                            [
                                'super_admin',
                                'admin',
                                'staff',
                                'customer_support',
                            ]
                        );
                }
            )
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.email',
            ]);

        $customerMessages = collect();

        if ($message->email) {
            $customerMessages = DB::table('contact_messages')
                ->where('email', $message->email)
                ->where('id', '!=', $message->id)
                ->orderByDesc('created_at')
                ->limit(8)
                ->get([
                    'id',
                    'contact_code',
                    'subject',
                    'type',
                    'status',
                    'priority',
                    'created_at',
                ]);
        }

        return view('admin.contact-messages.show', [
            'message' => $message,
            'assignees' => $assignees,
            'customerMessages' => $customerMessages,
            'statuses' => $this->statuses(),
            'types' => $this->types(),
            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Cập nhật trạng thái, độ ưu tiên và người xử lý.
     */
    public function updateStatus(
        Request $request,
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
                'status',
                'priority',
                'assigned_to',
                'replied_at',
                'closed_at',
            ]);

        abort_if(! $message, 404);

        $validated = $request->validate(
            [
                'status' => [
                    'required',
                    Rule::in(array_keys($this->statuses())),
                ],
                'priority' => [
                    'required',
                    Rule::in(array_keys($this->priorities())),
                ],
                'assigned_to' => [
                    'nullable',
                    'integer',
                    Rule::exists('users', 'id')
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'status.required' =>
                    'Vui lòng chọn trạng thái liên hệ.',
                'status.in' =>
                    'Trạng thái liên hệ không hợp lệ.',
                'priority.required' =>
                    'Vui lòng chọn mức ưu tiên.',
                'priority.in' =>
                    'Mức ưu tiên không hợp lệ.',
                'assigned_to.exists' =>
                    'Người xử lý không tồn tại.',
            ]
        );

        $newStatus = $validated['status'];

        $repliedAt = $message->replied_at;
        $closedAt = $message->closed_at;

        if ($newStatus === 'replied' && ! $repliedAt) {
            $repliedAt = now();
        }

        if ($newStatus === 'closed' && ! $closedAt) {
            $closedAt = now();
        }

        if (! in_array($newStatus, ['replied', 'closed'], true)) {
            $closedAt = null;
        }

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'status' => $newStatus,
                    'priority' => $validated['priority'],
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'replied_at' => $repliedAt,
                    'closed_at' => $closedAt,
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Cập nhật liên hệ thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật liên hệ: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật liên hệ.'
                );
        }
    }

    /**
     * Cập nhật ghi chú nội bộ.
     */
    public function updateNote(
        Request $request,
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
                'admin_note',
            ]);

        abort_if(! $message, 404);

        $validated = $request->validate(
            [
                'admin_note' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ],
            [
                'admin_note.max' =>
                    'Ghi chú nội bộ không được vượt quá 10.000 ký tự.',
            ]
        );

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'admin_note' => $this->nullableTrim(
                        $validated['admin_note'] ?? null
                    ),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Cập nhật ghi chú nội bộ thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật ghi chú: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật ghi chú.'
                );
        }
    }

    /**
     * Nhận xử lý liên hệ hiện tại.
     */
    public function assignToMe(
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
                'status',
            ]);

        abort_if(! $message, 404);

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'assigned_to' => auth()->id(),
                    'status' => $message->status === 'new'
                        ? 'processing'
                        : $message->status,
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Bạn đã nhận xử lý liên hệ này.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi nhận xử lý: '
                        . $exception->getMessage()
                    : 'Không thể nhận xử lý liên hệ.'
            );
        }
    }

    /**
     * Đánh dấu đã phản hồi.
     */
    public function markReplied(
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
                'assigned_to',
            ]);

        abort_if(! $message, 404);

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'status' => 'replied',
                    'assigned_to' => $message->assigned_to
                        ?? auth()->id(),
                    'replied_at' => now(),
                    'closed_at' => null,
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Đã đánh dấu liên hệ là đã phản hồi.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi cập nhật: '
                        . $exception->getMessage()
                    : 'Không thể đánh dấu đã phản hồi.'
            );
        }
    }

    /**
     * Đánh dấu đã đóng.
     */
    public function close(
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
                'assigned_to',
            ]);

        abort_if(! $message, 404);

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'status' => 'closed',
                    'assigned_to' => $message->assigned_to
                        ?? auth()->id(),
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Đã đóng liên hệ.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi đóng liên hệ: '
                        . $exception->getMessage()
                    : 'Không thể đóng liên hệ.'
            );
        }
    }

    /**
     * Đánh dấu thư rác.
     */
    public function markSpam(
        int $contactMessage
    ): RedirectResponse {
        $message = DB::table('contact_messages')
            ->where('id', $contactMessage)
            ->first([
                'id',
            ]);

        abort_if(! $message, 404);

        try {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->update([
                    'status' => 'spam',
                    'assigned_to' => auth()->id(),
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Đã đánh dấu liên hệ là thư rác.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi đánh dấu spam: '
                        . $exception->getMessage()
                    : 'Không thể đánh dấu thư rác.'
            );
        }
    }

    private function statuses(): array
    {
        return [
            'new' => 'Mới',
            'processing' => 'Đang xử lý',
            'replied' => 'Đã phản hồi',
            'closed' => 'Đã đóng',
            'spam' => 'Thư rác',
        ];
    }

    private function types(): array
    {
        return [
            'general' => 'Liên hệ chung',
            'order' => 'Đơn hàng',
            'product' => 'Sản phẩm',
            'payment' => 'Thanh toán',
            'shipping' => 'Vận chuyển',
            'return' => 'Đổi trả',
            'complaint' => 'Khiếu nại',
        ];
    }

    private function priorities(): array
    {
        return [
            'low' => 'Thấp',
            'normal' => 'Bình thường',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
