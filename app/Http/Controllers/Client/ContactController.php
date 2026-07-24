<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Services\Admin\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('client.contact.create', [
            'user' => $user,
            'types' => $this->types(),
        ]);
    }

    public function store(
        StoreContactMessageRequest $request
    ): RedirectResponse {
        $contactMessage = DB::transaction(function () use ($request): ContactMessage {
            $data = $request->validated();
            $user = $request->user();
            $orderId = null;

            if (! empty($data['order_code'])) {
                $order = Order::query()
                    ->where('order_code', $data['order_code'])
                    ->when(
                        $user,
                        fn ($query) => $query->where('user_id', $user->id)
                    )
                    ->whereNull('deleted_at')
                    ->first();

                if (! $order) {
                    throw ValidationException::withMessages([
                        'order_code' => 'Không tìm thấy đơn hàng phù hợp.',
                    ]);
                }

                $orderId = $order->id;
            }

            return ContactMessage::query()->create([
                'contact_code' => $this->generateContactCode(),
                'user_id' => $user?->id,
                'name' => $user?->name ?: $data['name'],
                'email' => $user?->email ?: $data['email'],
                'phone' => $data['phone'] ?: null,
                'type' => $data['type'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'order_id' => $orderId,
                'status' => 'new',
                'priority' => $data['type'] === 'complaint'
                    ? 'high'
                    : 'normal',
                'assigned_to' => null,
                'admin_note' => null,
                'replied_at' => null,
                'closed_at' => null,
            ]);
        }, 3);

        $this->notificationService->safely(
            fn () => $this->notificationService->notifyNewContact(
                $contactMessage->id,
                $contactMessage->contact_code,
                $contactMessage->subject,
                [
                    'type' => $contactMessage->type,
                    'name' => $contactMessage->name,
                    'email' => $contactMessage->email,
                ]
            )
        );

        return redirect()
            ->route('contact.create')
            ->with(
                'success',
                "Đã gửi liên hệ {$contactMessage->contact_code}. Cửa hàng sẽ phản hồi sớm nhất."
            );
    }

    private function generateContactCode(): string
    {
        do {
            $code = 'CT' . now()->format('YmdHis') . Str::upper(Str::random(5));
        } while (ContactMessage::query()->where('contact_code', $code)->exists());

        return $code;
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
}
