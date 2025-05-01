<?php

namespace App\Calcul;

use App\MISC\PHPHelper;

class Calcul {

    public static function ObtenirSommeTotale ($listeVente) {
        $sommeTotale = 0;

        for($i = 0; $i < count($listeVente); $i++){
            $sommeTotale += $listeVente[$i]->getSommeTotale();
        }

        return $sommeTotale;
    }
}