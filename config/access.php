<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pengecekan Hak Akses backoffice
    |--------------------------------------------------------------------------
    |
    | false: semua yang login bisa membuka semua menu (seperti sebelumnya), tapi
    | sidebar sudah dibentuk dari tabel menu. Nyalakan setelah centangan Hak Akses
    | di menu Administrasi > Hak Akses sudah dirapikan.
    |
    */
    'enforced' => (bool) env('ACCESS_CONTROL_ENFORCED', false),
];
