<?php


namespace App\Interfaces;


interface TransliteratorInterface
{
    public function transliterate(string $string): string;
}