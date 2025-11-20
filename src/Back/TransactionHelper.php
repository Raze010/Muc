<?php

namespace App\Back;

use App\Back\Graphique\GenGraphique;
use App\Back\Calcul;
use App\Back\LectureTransactionEtoro;
use App\Entity\Vente;
use App\Repository\VenteRepository;
use DateTime;
use PhpParser\Node\Expr\Array_;
use Proxies\__CG__\App\Entity\Cours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\MISC\PHPHelper;
use App\Entity\Utilisateur;

class TransactionHelper
{
    public $modeGP, $modeAffichage, $cours;

    public function FiltrerListeVente($listeVente)
    {
        $listeVenteFiltrer = [];

        foreach ($listeVente as $vente) {
            $gp = $vente->getGP();

            if ($this->modeGP == "gain" && $gp <= 0) {
                continue;
            } else if ($this->modeGP == "perte" && $gp >= 0) {
                continue;
            }

            if ($this->cours != "") {
                if ($vente->getCours()->getSurnom() != $this->cours) {
                    continue;
                }
            }

            $listeVenteFiltrer[] = $vente;
        }

        return $listeVenteFiltrer;
    }

    public function ObtenirListeVente_JS($listeVente)
    {
        usort($listeVente, function (Vente $a, Vente $b) {
            return $a->getDateVente() <=> $b->getDateVente();
        });

        if(count($listeVente) == 0) {
            return $listeVente;
        }

        $premiereTransaction = $listeVente[0];

        $dateDebut = clone $premiereTransaction->getDateVente();

        if ($this->modeAffichage == "transaction") {
            $dateDebut->modify('-1 day');
        } else if ($this->modeAffichage == "jour") {
            $dateDebut->modify('-1 day');
        } else if ($this->modeAffichage == "mois") {
            $dateDebut->modify('-1 month');
        } else if ($this->modeAffichage == "an") {
            $dateDebut->modify('-1 year');
        }

        $objetActu = [
            'idTransaction' => 0,
            'ordre' => 0,
            'gp' => 0,
            'gpTotale' => 0,
            'date' => $dateDebut->format(DateTime::ATOM),
            'cours_nom' => "",
            'cours_image' => null,
            'levier' => 1,
            'graphe_x' => -1,
            'graphe_y' => -1
        ];

        $listeVenteJS[] = $objetActu;

        $objetActu['ordre'] = 1;
        $objetActu['date'] = null;

        $dateFormaterActuelle = "";

        $ordre = 2;
        $i = 0;

        foreach ($listeVente as $vente) {
            $gp = $vente->getGP();

            $Ajouter = false;

            $copieDate = clone $vente->getDateVente();

            if ($this->modeAffichage == "transaction") {
                $objetActu["idTransaction"] = $vente->getIdTransaction();
                $objetActu["gpTotale"] += $gp;
                $objetActu["gp"] += $gp;
                $objetActu["date"] = $copieDate->format(DateTime::ATOM);
                $objetActu['cours_nom'] = $vente->getCours()->getSurnom();
                $objetActu['levier'] = $vente->getEffetLevier();

                $Ajouter = true;
            } else {

                if ($this->modeAffichage == "jour") {
                    $dateFormaterVente = $vente->getDateVente()->format('Y-m-d');

                    $copieDate->setDate($copieDate->format('Y'), $copieDate->format('m'), $copieDate->format('d'));
                } else if ($this->modeAffichage == "mois") {
                    $dateFormaterVente = $vente->getDateVente()->format('Y-m');

                    $copieDate->setDate($copieDate->format('Y'), $copieDate->format('m'), 1);
                } else if ($this->modeAffichage == "an") {
                    $dateFormaterVente = $vente->getDateVente()->format('Y');

                    $copieDate->setDate($copieDate->format('Y'), 1, 1);
                }

                if ($dateFormaterActuelle == "" || $dateFormaterVente == $dateFormaterActuelle) {
                    $objetActu["gp"] += $gp;
                    $objetActu["gpTotale"] += $gp;
                    if ($objetActu["date"] == null) {
                        $objetActu["date"] = $copieDate->format(DateTime::ATOM);
                    }
                    if ($i + 1 >= count(value: $listeVente)) {
                        $Ajouter = true;
                    }
                } else {
                    $Ajouter = true;
                }

                $dateFormaterActuelle = $dateFormaterVente;
            }

            if ($Ajouter == true) {
                $gpTotale = $objetActu["gpTotale"];

                if ($this->modeAffichage == "transaction") {
                    $listeVenteJS[] = $objetActu;

                    $ordre += 1;

                    $objetActu = [
                        'ordre' => $ordre,
                        'gp' => 0,
                        'gpTotale' => $gpTotale,
                        'date' => $copieDate->format(DateTime::ATOM),
                        'cours_nom' => "",
                        'cours_image' => null,
                        'levier' => 1,
                        'graphe_x' => -1,
                        'graphe_y' => -1
                    ];
                } else {
                    $gpTotale = $gpTotale + $gp;

                    $listeVenteJS[] = $objetActu;

                    $ordre += 1;

                    $objetActu = [
                        'ordre' => $ordre,
                        'gp' => $gp,
                        'gpTotale' => $gpTotale,
                        'date' => $copieDate->format(DateTime::ATOM),
                        'cours_nom' => "",
                        'cours_image' => null,
                        'levier' => 1,
                        'graphe_x' => -1,
                        'graphe_y' => -1
                    ];
                }
            }

            $i++;
        }

        return $listeVenteJS;
    }

    public function ObtenirListeCours($listeVente)
    {
        $listeCours = [];

        // [
        //     'nomCours' => "",
        //     'gpTotale' => 0,
        //     'nbTransaction' => 0,
        //     'nbTransactionPos => 0,
        //     'nbTransactionNeg => 0,
        //     'pourcentage' => 0,
        //     'EstUnGain' => 0
        // ];

        foreach ($listeVente as $vente) {
            $cours = $vente->getCours();

            $cours_surnom = $cours->getSurnom();

            $coursTab = null;

            if (array_key_exists($cours_surnom, $listeCours)) {
                $coursTab = $listeCours[$cours_surnom];
            } else {
                $coursTab = [
                    'coursPHP' => $cours,
                    'nomCours' => $cours_surnom,
                    'gpTotale' => 0,
                    'nbTransaction' => 0,
                    'nbTransactionPos' => 0,
                    'nbTransactionNeg' => 0,
                    'pourcentage' => 0,
                    'EstUnGain' => 0,
                    'ListeIdTransaction' => []
                ];
            }

            $gp = $vente->getGP();

            $coursTab['gpTotale'] += $gp;
            $coursTab['nbTransaction'] += 1;
            if($gp >= 0) {
                $coursTab['nbTransactionPos'] += 1;
            } else {
                $coursTab['nbTransactionNeg'] += 1;
            }

            $coursTab['ListeIdTransaction'][] = $vente->getIdTransaction();

            $gain = $coursTab['gpTotale'] > 0;

            $coursTab['EstUnGain'] = $gain;

            $listeCours[$cours_surnom] = $coursTab;
        }

        $gainCumuler = 0;
        $perteCumuler = 0;

        foreach ($listeCours as $cours) {
            if ($cours['EstUnGain']) {
                $gainCumuler += $cours['gpTotale'];
            } else {
                $perteCumuler -= $cours['gpTotale'];
            }
        }

        foreach ($listeCours as $cours) {
            if ($cours['EstUnGain']) {
                $cours['pourcentage'] = round($cours['gpTotale'] / $gainCumuler * 100);
            } else {
                $cours['pourcentage'] = -round($cours['gpTotale'] / $perteCumuler * 100);
            }

            $listeCours[$cours['coursPHP']->getSurnom()] = $cours;
        }

        usort($listeCours, function ($a, $b) {
            return $b['gpTotale'] <=> $a['gpTotale'];
        });

        return $listeCours;
    }
}
