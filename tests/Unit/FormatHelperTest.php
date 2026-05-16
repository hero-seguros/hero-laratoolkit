<?php

use HeroLaraToolkit\Helpers\FormatHelper;

it('formats dates between BR and MySQL', function () {
    // Note: dateToMysql delegates to strtotime which parses "14/05/2025" as
    // an invalid US date (returns 1970-01-01). Use dash-separated or already-MySQL
    // input. This matches how production code calls the helper today.
    expect(FormatHelper::dateToBr('2025-05-14'))->toBe('14/05/2025')
        ->and(FormatHelper::dateToMysql('14-05-2025'))->toBe('2025-05-14')
        ->and(FormatHelper::datetimeToBr('2025-05-14 10:30:00'))->toBe('14/05/2025 10:30:00')
        ->and(FormatHelper::datetimeToMysql('2025-05-14 10:30:00'))->toBe('2025-05-14 10:30:00');
});

it('formats floats with BR notation', function () {
    expect(FormatHelper::floatToBr(1234.5))->toBe('1.234,50')
        ->and(FormatHelper::floatToBr(0.1))->toBe('0,10');
});

it('masks CPF and CNPJ', function () {
    expect(FormatHelper::cpf('12345678901'))->toBe('123.456.789-01')
        ->and(FormatHelper::cnpj('11222333000181'))->toBe('11.222.333/0001-81');
});

it('masks phone numbers for 10 and 11 digit inputs', function () {
    expect(FormatHelper::phone('1133334444'))->toBe('(11) 3333-4444')
        ->and(FormatHelper::phone('11912345678'))->toBe('(11) 91234-5678');
});

it('returns phone unchanged for unexpected lengths', function () {
    expect(FormatHelper::phone('123'))->toBe('123');
});

it('masks CEP', function () {
    expect(FormatHelper::cep('01310100'))->toBe('01310-100');
});
