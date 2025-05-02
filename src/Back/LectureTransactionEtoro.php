<?php

namespace App\Back;

use App\Entity\Cours;
use App\Entity\Vente;
use App\MISC\PHPHelper;
use App\Repository\CoursRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\Persistence\ManagerRegistry;
use Proxies\__CG__\App\Entity\Utilisateur;

class LectureTransactionEtoro {

    public static function LireFloatXls ($floatStr){
        if(strlen($floatStr) == 0)
            return 0;
        
        if($floatStr[0] == '('){
            $floatStr = substr($floatStr, 1, -1);

            return -1 * (float)$floatStr;
        } else {
            return (float)$floatStr;
        }
    }

    public static function Lire ($fichier, $utilisateur,ManagerRegistry $doctrine) {

        $utilisateur = $doctrine->getRepository(Utilisateur::class)->find($utilisateur->getId());

        $fichierLoaded = IOFactory::load($fichier);
        $fichierDonnee = $fichierLoaded->getSheet(1);
        $listeDonnee = $fichierDonnee->toArray();
        
        $tailleData = count($listeDonnee);

        $repo_cours = $doctrine->getRepository(Cours::class);

        $listeCours = $repo_cours->getAll();

        $repo_vente = $doctrine->getRepository(Vente::class);

        $listeVente = $repo_vente->TrouverVente($utilisateur);

        $tailleVente = count($listeVente);

        for ($i = 1; $i < $tailleData; $i++) {
            $donnee = $listeDonnee[$i];

            $transactionId = $donnee[0];

            $existeDeja = false;

            for ($o = 0; $o < $tailleVente; $o++) {
                $venteEnregistrer = $listeVente[$o];

                if ($venteEnregistrer->getIdTransaction() === $transactionId) {
                    $existeDeja = true;
                    break;
                }
            }

            if($existeDeja){
                continue;
            }

            $nomCours = $donnee[1];
            $EstUnShort = $donnee[2] === 'Short';
            $sommeInvestis = LectureTransactionEtoro::LireFloatXls($donnee[3]);

            $formatDate = 'd/m/Y H:i:s';

            $dateAchat = DateTime::createFromFormat($formatDate,$donnee[5]);
            $dateVente = DateTime::createFromFormat($formatDate,$donnee[6]);
            $effetDeLevier = (int)$donnee[7];

            $gpBrut = LectureTransactionEtoro::LireFloatXls($donnee[10]);

            $prixAchat = LectureTransactionEtoro::LireFloatXls($donnee[14]);
            $prixVente = LectureTransactionEtoro::LireFloatXls($donnee[15]);

            $fraisDividende = LectureTransactionEtoro::LireFloatXls($donnee[18]);

            $vente = new Vente();

            //IdTransaction
            $vente->setIdTransaction($transactionId);

            //Est un short
            $vente->setEstUnShort($EstUnShort);

            //Utilisateur
            $vente->setUtilisateur($utilisateur);

            //Cours
            $coursTrouver = false;

            foreach ($listeCours as $cours) {
                if ($cours->getNom() === $nomCours) {
                    $coursTrouver = true;
                    $vente->setCours($cours);
                    break;
                }
            }

            if(!$coursTrouver) {
                $cours = new Cours();

                $cours->setNom($nomCours);
                
                $vente->setCours($cours);
            
                $repo_cours->addCours($cours);
                
                array_push($listeCours,$cours);
            }

            //Somme investis
            $vente->setSommeInvestis($sommeInvestis);

            //GP
            $gpReel = $gpBrut - $fraisDividende - 2;

            $vente->setGP($gpReel);

            //Achat
            $vente->setDateAchat($dateAchat);
            $vente->setPrixAchat($prixAchat);

            //Vente
            $vente->setDateVente($dateVente);
            $vente->setPrixVente($prixVente);

            //Effet de levier
            $vente->setEffetLevier($effetDeLevier);

            $repo_vente->addVente($vente);

            array_push($listeVente,$vente);
        }
    }
}