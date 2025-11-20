<?php

namespace App\Back;

use App\MISC\PHPHelper;
use PhpParser\Node\Expr\Array_;

class Calcul
{

    public static function getGpTotale($listeVente)
    {
        $sommeTotale = 0;

        for ($i = 0; $i < count($listeVente); $i++) {
            $sommeTotale += $listeVente[$i]->getGP();
        }

        return $sommeTotale;
    }

    public static function getMaxMin($listeVente)
    {
        $plusGrand = -10000000000000000;
        $plusPetit = 10000000000000000;

        for ($i = 0; $i < count($listeVente); $i++) {
            $vente = $listeVente[$i];

            $gpTotale = $vente['gpTotale'];

            if ($plusGrand < $gpTotale) {
                $plusGrand = $gpTotale;
            }
            if ($plusPetit > $gpTotale) {
                $plusPetit = $gpTotale;
            }
        }

        return ['max' => $plusGrand, 'min' => $plusPetit];
    }
    
    public static function getGpTotale_Abs($listeVente)
    {
        $sommeTotale = 0;

        for ($i = 0; $i < count($listeVente); $i++) {
            $sommeTotale += abs($listeVente[$i]->getGP());
        }

        return $sommeTotale;
    }

}