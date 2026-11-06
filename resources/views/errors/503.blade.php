{{-- Dipakai juga oleh `php artisan down`, saat aplikasi belum bisa merender Inertia. --}}
@include('errors.fallback', ['status' => 503, 'message' => null])
