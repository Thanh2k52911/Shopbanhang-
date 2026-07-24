<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportReplyRequest;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportChatController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('keyword'));
        $status = trim((string) $request->query('status'));

        $conversations = SupportConversation::query()
            ->with([
                'user:id,name,email',
                'assignedAdmin:id,name',
            ])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) =>
                    $query->where('is_read_by_shop', false),
            ])
            ->when(
                $keyword !== '',
                fn ($query) => $query->whereHas(
                    'user',
                    fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                )
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.support-chats.index',
            compact(
                'conversations',
                'keyword',
                'status'
            )
        );
    }

    public function show(
        SupportConversation $supportConversation
    ): View {
        $supportConversation->load([
            'user',
            'assignedAdmin',
            'messages' => fn ($query) => $query
                ->with('sender.roles')
                ->orderBy('id'),
        ]);

        SupportMessage::query()
            ->where(
                'conversation_id',
                $supportConversation->id
            )
            ->where('sender_id', '!=', auth()->id())
            ->update([
                'is_read_by_shop' => true,
            ]);

        return view(
            'admin.support-chats.show',
            compact('supportConversation')
        );
    }

    public function reply(
        StoreSupportReplyRequest $request,
        SupportConversation $supportConversation
    ): RedirectResponse {
        $adminId = (int) auth()->id();

        SupportMessage::query()->create([
            'conversation_id' => $supportConversation->id,
            'sender_id' => $adminId,
            'message' => $request->validated('message'),
            'is_read_by_customer' => false,
            'is_read_by_shop' => true,
        ]);

        $supportConversation->forceFill([
            'assigned_admin_id' =>
                $supportConversation->assigned_admin_id
                ?: $adminId,
            'status' => 'waiting_customer',
            'last_message_at' => now(),
            'closed_at' => null,
        ])->save();

        return back()->with(
            'success',
            'Đã gửi phản hồi tới khách hàng.'
        );
    }

    public function assign(
        SupportConversation $supportConversation
    ): RedirectResponse {
        $supportConversation->forceFill([
            'assigned_admin_id' => auth()->id(),
        ])->save();

        return back()->with(
            'success',
            'Bạn đã nhận xử lý cuộc trò chuyện này.'
        );
    }

    public function close(
        SupportConversation $supportConversation
    ): RedirectResponse {
        $supportConversation->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        return back()->with(
            'success',
            'Đã đóng cuộc trò chuyện.'
        );
    }

    public function reopen(
        SupportConversation $supportConversation
    ): RedirectResponse {
        $supportConversation->forceFill([
            'status' => 'open',
            'closed_at' => null,
        ])->save();

        return back()->with(
            'success',
            'Đã mở lại cuộc trò chuyện.'
        );
    }

    public function messages(
        SupportConversation $supportConversation
    ): JsonResponse {
        $supportConversation->load([
            'messages' => fn ($query) => $query
                ->with('sender.roles')
                ->orderBy('id'),
        ]);

        SupportMessage::query()
            ->where(
                'conversation_id',
                $supportConversation->id
            )
            ->where('sender_id', '!=', auth()->id())
            ->update([
                'is_read_by_shop' => true,
            ]);

        return response()->json([
            'success' => true,
            'conversation_status' =>
                $supportConversation->status,
            'messages' => $supportConversation->messages
                ->map(fn (SupportMessage $message): array => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_name' => $message->sender?->name
                        ?? 'Người dùng',
                    'is_shop' => $message->is_shop_message,
                    'created_at' => $message->created_at
                        ->format('d/m/Y H:i'),
                ])
                ->values(),
        ]);
    }
}
