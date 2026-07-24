<footer class="border-t border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8">

    <div
        class="flex flex-col items-center justify-between gap-2 text-center text-sm text-gray-500 sm:flex-row sm:text-left"
    >
        {{-- Bản quyền --}}
        <p>
            &copy; {{ now()->year }}

            <span class="font-medium text-gray-700">
                {{ config('app.name', 'Cosmetic Shop') }}
            </span>

            . Bảo lưu mọi quyền.
        </p>

        {{-- Phiên bản hệ thống --}}
        <p>
            Hệ thống quản trị

            <span class="font-medium text-pink-600">
                Cosmetic Shop
            </span>
        </p>
    </div>

</footer>
