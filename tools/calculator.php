<?php

class Calculator
{
    public static function Gain($resultat, $mise, $cote)
    {
        switch ($resultat) {
            case 1:
                return ($cote - 1) * $mise;
            case 2:
                return -$mise;
            case 3:
                return 0;
            }

        return '';
    }

    static function Yield($mise, $profit) {
        $out = $mise != 0 ? sprintf('%.2f', ($profit / $mise) * 100) : '0.00';

        return $out;
    }
}
