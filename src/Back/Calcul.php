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

    public static function trierVenteParModeAffichage($listeVente, $modeAffichage)
    {
        $listeTrier = [];

        if ($modeAffichage == "transaction") {
            return $listeVente;
        }

        $idActuelle = null;

        if ($modeAffichage == 'jour') {
            $format = 'Y-n-j';
        } else if ($modeAffichage == 'semaine') {
            $format = 'o-W';
        } else if ($modeAffichage == 'mois') {
            $format = 'Y-n';
        } else if ($modeAffichage == 'an') {
            $format = 'Y';
        }

        $listeTransactionRegroupe  = [];

        foreach ($listeVente as $vente) {
            $id = $vente->getDateVente()->format($format);

            if ($id != $idActuelle) {
                if (count($listeTransactionRegroupe ) > 0) {
                    $listeTrier[] = $listeTransactionRegroupe;
                }
                $listeTransactionRegroupe = [];
                $idActuelle = $id;
            }

            $listeTransactionRegroupe[] = $vente;
        }

        if (count($listeTransactionRegroupe) > 0) {
            $listeTrier[] = $listeTransactionRegroupe;
        }

        return $listeTrier;
    }
}