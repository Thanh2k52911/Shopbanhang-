<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-2xl">
                    ✉️
                </div>

                <h1 class="mt-4 text-2xl font-bold text-gray-900">
                    Xác minh email
                </h1>

                <p class="mt-2 text-sm leading-6 text-gray-600">
                    Mã xác minh 6 số đã được gửi tới
                    <strong class="text-gray-900">{{ $email }}</strong>.
                </p>
            </div>

            @if (session('success'))
                <div class="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4">
                    <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('verification.otp.verify') }}"
                class="mt-6 space-y-5"
            >
                @csrf

                <div>
                    <label
                        for="otp"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Mã xác minh
                    </label>

                    <input
                        id="otp"
                        type="text"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                        name="otp"
                        value="{{ old('otp') }}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        autofocus
                        required
                        placeholder="000000"
                        class="w-full rounded-xl border-gray-300 px-4 py-3 text-center text-2xl font-bold tracking-[0.45em] focus:border-pink-500 focus:ring-pink-500"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-pink-600 px-4 py-3 text-sm font-semibold text-white hover:bg-pink-700"
                >
                    Xác minh email
                </button>
            </form>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form
                    method="POST"
                    action="{{ route('verification.otp.send') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="text-sm font-semibold text-pink-600 hover:text-pink-700"
                    >
                        Gửi lại mã
                    </button>
                </form>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="text-sm font-medium text-gray-500 underline hover:text-gray-700"
                    >
                        Đăng xuất
                    </button>
                </form>
            </div>

            <p class="mt-5 text-center text-xs leading-5 text-gray-500">
                Mã có hiệu lực trong 10 phút và tối đa 5 lần nhập sai.
            </p>
        </div>
    </div>
</x-guest-layout>
