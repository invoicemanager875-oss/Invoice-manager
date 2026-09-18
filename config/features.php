<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Drafter Tasks
    |--------------------------------------------------------------------------
    |
    | Mengontrol fitur role "drafter" + checklist tugas pada Form Order
    | (Kelola Drafter, assignment PIC per lingkup pekerjaan, halaman
    | "Task Drafter"). Default false agar tidak muncul di production sampai
    | siap diaktifkan lewat FEATURE_DRAFTER_TASKS=true di .env.
    |
    */
    'drafter_tasks' => env('FEATURE_DRAFTER_TASKS', false),

];
