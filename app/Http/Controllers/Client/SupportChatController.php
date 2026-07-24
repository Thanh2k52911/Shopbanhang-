<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreSupportMessageRequest;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportChatController extends Controller
{
    public function show(): View
    {
        $conversation = SupportConversation::query()
            ->with([
                'assignedAdmin.roles',
                'messages' => fn ($query) => $query
                    ->with('sender.roles')
                    ->orderBy('id'),
            ])
            ->forCustomer((int) auth()->id())
            ->latest('id')
            ->first();

        if ($conversation) {
            SupportMessage::query()
                ->where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', auth()->id())
                ->update([
                    'is_read_by_customer' => true,
                ]);
        }

        return view(
            'client.account.support-chat.show',
            compact('conversation')
        );
    }

    public function store(
        StoreSupportMessageRequest $request
    ): RedirectResponse {
        $userId = (int) auth()->id();

        $conversation = SupportConversation::query()
            ->forCustomer($userId)
            ->open()
            ->latest('id')
            ->first();

        if (! $conversation) {
            $conversation = SupportConversation::query()
                ->create([
                    'user_id' => $userId,
                    'subject' => 'Hỗ trợ khách hàng',
                    'status' => 'waiting_shop',
                    'last_message_at' => now(),
                ]);
        }

        SupportMessage::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'message' => $request->validated('message'),
            'is_read_by_customer' => true,
            'is_read_by_shop' => false,
        ]);

        $conversation->forceFill([
            'status' => 'waiting_shop',
            'last_message_at' => now(),
            'closed_at' => null,
        ])->save();

        return back()->with(
            'success',
            'Tin nhắn đã được gửi tới cửa hàng.'
        );
    }

    public function messages(): JsonResponse
    {
        $conversation = SupportConversation::query()
            ->with([
                'messages' => fn ($query) => $query
                    ->with('sender.roles')
                    ->orderBy('id'),
            ])
            ->forCustomer((int) auth()->id())
            ->latest('id')
            ->first();

        if (! $conversation) {
            return response()->json([
                'success' => true,
                'messages' => [],
            ]);
        }

        SupportMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->update([
                'is_read_by_customer' => true,
            ]);

        return response()->json([
            'success' => true,
            'conversation_status' => $conversation->status,
            'messages' => $conversation->messages
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
