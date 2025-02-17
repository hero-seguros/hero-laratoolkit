<?php

namespace HeroLaraToolkit\Helpers;

class ValidatorHelper
{
    public static function cpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public static function cnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14) {
            return false;
        }

        $sum = 0;
        $length = strlen($cnpj) - 2;

        for ($t = 12; $t >= 1; $t--) {
            $sum += $cnpj[$length - $t] * $t;
        }

        $mod = $sum % 11;
        $digit = $mod < 2 ? 0 : 11 - $mod;

        if ($cnpj[$length] != $digit) {
            return false;
        }

        $sum = 0;
        $length++;

        for ($t = 13; $t >= 1; $t--) {
            $sum += $cnpj[$length - $t] * $t;
        }

        $mod = $sum % 11;
        $digit = $mod < 2 ? 0 : 11 - $mod;

        if ($cnpj[$length] != $digit) {
            return false;
        }

        return true;
    }

    public static function phone(string $telefone): bool
    {
        $telefone = preg_replace('/\D/', '', $telefone);

        if (strlen($telefone) !== 10) {
            return false;
        }

        $ddd = substr($telefone, 0, 2);
        $numero = substr($telefone, 2);

        if ($ddd < '11' || $ddd > '99') {
            return false;
        }

        if (!preg_match('/^[2-5]/', $numero)) {
            return false;
        }

        return true;
    }


    public static function cellphone(string $celular): bool
    {
        $celular = preg_replace('/\D/', '', $celular);

        if (strlen($celular) !== 11) {
            return false;
        }

        $ddd = substr($celular, 0, 2);
        $numero = substr($celular, 2);

        if ($ddd < '11' || $ddd > '99') {
            return false;
        }

        if ($numero[0] !== '9') {
            return false;
        }

        if ($numero[1] === '0' || $numero[1] === '1') {
            return false;
        }

        return true;
    }


    public static function cep(string $cep): bool
    {
        $cep = preg_replace('/\D/', '', $cep);
        if (strlen($cep) !== 8) {
            return false;
        }
        if (preg_match('/^(\d)\1{7}$/', $cep)) {
            return false;
        }

        return true;
    }


    public static function passport(string $passaporte): bool
    {
        return preg_match('/^[A-Za-z]{2}\d{6}$/i', $passaporte) > 0;
    }
}
