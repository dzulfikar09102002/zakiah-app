<?php

use App\Services\ReportPdfDocument;

it('memakai palet warna sesuai tema entity', function (string $theme, array $primary) {
    expect((new ReportPdfDocument('landscape', $theme))->color('primary'))->toBe($primary);
})->with([
    'zakiah: navy' => ['zakiah', [47, 62, 86]],
    'secaca: teal' => ['secaca', [31, 127, 140]],
    'tema tak dikenal memakai zakiah' => ['duo', [47, 62, 86]],
]);

it('memakai tema zakiah bila tema tidak diberikan', function () {
    expect((new ReportPdfDocument('portrait'))->color('accent'))->toBe([201, 160, 102]);
});
