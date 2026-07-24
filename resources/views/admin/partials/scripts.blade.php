{{-- JavaScript dùng chung cho toàn bộ khu vực Admin --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        /*
        |--------------------------------------------------------------------------
        | Tự động ẩn thông báo
        |--------------------------------------------------------------------------
        |
        | Các thông báo có class "admin-alert" sẽ tự biến mất sau 5 giây.
        |
        */

        const alerts = document.querySelectorAll('.admin-alert');

        alerts.forEach(function (alert) {
            setTimeout(function () {
                alert.classList.add(
                    'opacity-0',
                    '-translate-y-2'
                );

                setTimeout(function () {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });
</script>
