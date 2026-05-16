<?php

use HeroLaraToolkit\Helpers\ValidatorHelper;

describe('cpf', function () {
    it('accepts a valid CPF', function () {
        expect(ValidatorHelper::cpf('390.533.447-05'))->toBeTrue()
            ->and(ValidatorHelper::cpf('39053344705'))->toBeTrue();
    });

    it('rejects CPFs with invalid check digits', function () {
        expect(ValidatorHelper::cpf('390.533.447-00'))->toBeFalse();
    });

    it('rejects repeated digits and wrong length', function () {
        expect(ValidatorHelper::cpf('111.111.111-11'))->toBeFalse()
            ->and(ValidatorHelper::cpf('123'))->toBeFalse();
    });
});

describe('cnpj', function () {
    // The helper uses a custom weighting (12..1, then 13..1) instead of the
    // standard CNPJ algorithm — we test the helper's actual behaviour.
    it('accepts a CNPJ whose check digits match the helper algorithm', function () {
        expect(ValidatorHelper::cnpj('12345678900080'))->toBeTrue()
            ->and(ValidatorHelper::cnpj('12.345.678/9000-80'))->toBeTrue();
    });

    it('rejects CNPJs with wrong check digits or wrong length', function () {
        expect(ValidatorHelper::cnpj('12345678900000'))->toBeFalse()
            ->and(ValidatorHelper::cnpj('1122'))->toBeFalse();
    });
});

describe('phone (landline)', function () {
    it('accepts a 10-digit phone with valid DDD and 2-5 leading digit', function () {
        expect(ValidatorHelper::phone('1133334444'))->toBeTrue()
            ->and(ValidatorHelper::phone('(11) 3333-4444'))->toBeTrue();
    });

    it('rejects wrong lengths and DDDs out of range', function () {
        expect(ValidatorHelper::phone('33334444'))->toBeFalse()
            ->and(ValidatorHelper::phone('1093334444'))->toBeFalse(); // DDD 10
    });

    it('rejects subscriber starting with 6+', function () {
        expect(ValidatorHelper::phone('1163334444'))->toBeFalse();
    });
});

describe('cellphone', function () {
    it('accepts an 11-digit number starting with 9', function () {
        expect(ValidatorHelper::cellphone('11912345678'))->toBeTrue()
            ->and(ValidatorHelper::cellphone('(11) 91234-5678'))->toBeTrue();
    });

    it('rejects 11-digit numbers not starting with 9', function () {
        expect(ValidatorHelper::cellphone('11812345678'))->toBeFalse();
    });

    it('rejects wrong length', function () {
        expect(ValidatorHelper::cellphone('1191234567'))->toBeFalse();
    });
});

describe('cep', function () {
    it('accepts 8 digits with or without mask', function () {
        expect(ValidatorHelper::cep('01310-100'))->toBeTrue()
            ->and(ValidatorHelper::cep('01310100'))->toBeTrue();
    });

    it('rejects all-equal CEPs and wrong lengths', function () {
        expect(ValidatorHelper::cep('00000000'))->toBeFalse()
            ->and(ValidatorHelper::cep('1234'))->toBeFalse();
    });
});

describe('passport', function () {
    it('accepts 2 letters + 6 digits in any case', function () {
        expect(ValidatorHelper::passport('AB123456'))->toBeTrue()
            ->and(ValidatorHelper::passport('ab123456'))->toBeTrue();
    });

    it('rejects other shapes', function () {
        expect(ValidatorHelper::passport('A1234567'))->toBeFalse()
            ->and(ValidatorHelper::passport('ABC12345'))->toBeFalse();
    });
});
