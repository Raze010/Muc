<?php

namespace App\Back;

use App\MISC\PHPHelper;

class Calcul {

    public static function getGpTotale ($listeVente) {
        $sommeTotale = 0;

        for($i = 0; $i < count($listeVente); $i++){
            $sommeTotale += $listeVente[$i]->getGP();
        }

        return $sommeTotale;
    }
}