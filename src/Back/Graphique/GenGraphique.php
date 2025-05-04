<?php

namespace App\Back\Graphique;

use App\Entity\Vente;
use App\MISC\PHPHelper;
use DateTime;
use PhpParser\Node\Expr\Array_;

class GenGraphique
{

    public function remplirDonnee_vente($listeVente)
    {
        $this->DateValeurArray = [];

        usort($listeVente, function (Vente $a, Vente $b) {
            return $a->getDateVente() <=> $b->getDateVente();
        });

        $valeurActuelle = 0;

        foreach ($listeVente as $vente) {

            $gp = $vente->getGP();

            $valeurActuelle += $gp;

            $this->DateValeurArray[] = new DateValeur($vente->getDateVente(), $valeurActuelle, $gp);
        }
    }

    public $DateValeurArray, $largeur, $hauteur, $image;
    public $blanc, $vert, $rouge, $grey;
    public $plusPetiteDateTick, $plusGrandeDateTick;
    public $plusPetiteValeurMarger, $plusGrosseValeurMarger;

    public function generer($largeur, $hauteur, $dateDebut, $prixDebut, $dateFin, $prixFin)
    {
        $this->largeur = $largeur;
        $this->hauteur = $hauteur;

        //Creation de l'image
        $this->image = imagecreatetruecolor($largeur, $hauteur);

        $this->blanc = imagecolorallocate($this->image, 255, 255, 255);
        $this->vert = imagecolorallocate($this->image, 0, 255, 0);
        $this->rouge = imagecolorallocate($this->image, 255, 0, 0);
        $this->grey = imagecolorallocate($this->image, 158, 158, 158);

        $debugMessage = "";

        //Creation du graphe

        //Nombre de pixel par tick date
        $ListeDateValeur = $this->DateValeurArray;

        $nbValeur = count($ListeDateValeur);

        if ($nbValeur == 0) {
            imagepng($this->image);
            imagedestroy($this->image);
            return;
        }

        $this->plusPetiteDateTick = $ListeDateValeur[0]->date->getTimeStamp();
        $this->plusGrandeDateTick = $ListeDateValeur[$nbValeur - 1]->date->getTimeStamp();

        $min = $ListeDateValeur[0]->valeur;
        $max = $ListeDateValeur[0]->valeur;

        foreach ($ListeDateValeur as $DV) {
            if ($DV->valeur < $min) {
                $min = $DV->valeur;
            }
            if ($DV->valeur > $max) {
                $max = $DV->valeur;
            }
        }

        $additionalHeight = 200;

        $this->plusPetiteValeurMarger = $min - $additionalHeight;
        $this->plusGrosseValeurMarger = $max + $additionalHeight;

        $dernierX = 0;
        $dernierY = 0;

        //Dessin des lignes grise
        $ecart = 500;

        $prixDiv = (int)($this->plusGrosseValeurMarger / $ecart);

        while($prixDiv > $this->plusPetiteValeurMarger) {
            $this->DessinerLigneHorizon($prixDiv, "" . $prixDiv,15, $this->grey,25,50,"centre",null);

            $prixDiv -= $ecart;
        }

        //Dessin des record bas et haut
        for ($i = 0; $i < $nbValeur; $i++) {
            $dateValeur = $ListeDateValeur[$i];

            $date = $dateValeur->date;
            $valeur = $dateValeur->valeur;

            if($valeur == $max){
                $this->DessinerLigneHorizon($valeur, "Gain record: " . $valeur. " $",15, $this->vert, 50,75,"haut",$date);
            } else if($valeur == $min) {
                $this->DessinerLigneHorizon($valeur, "Perte record: " . $valeur . " $",15, $this->rouge,50,75,"bas",$date);
            }
        }

        //Dessin des lignes entre transactions
        for ($i = 0; $i < $nbValeur; $i++) {
            $dateValeur = $ListeDateValeur[$i];

            $date = $dateValeur->date;
            $valeur = $dateValeur->valeur;

            $x = $this->CalculerXDate($date);
            $y = $this->CalculerYValeur($valeur);

            $couleurLigne = $valeur > 0 ? $this->vert : $this->rouge;

            if ($i > 0) {
                imageline($this->image, $dernierX, $dernierY, $x, $y, $couleurLigne);
            }

            $dernierX = $x;
            $dernierY = $y;
        }

        //Dessin des points des transactions
        for ($i = 0; $i < $nbValeur; $i++) {
            $dateValeur = $ListeDateValeur[$i];

            $date = $dateValeur->date;
            $valeur = $dateValeur->valeur;
            $gain = $dateValeur->gain;

            $x = $this->CalculerXDate($date);
            $y = $this->CalculerYValeur($valeur);

            $absGain = $gain < 0 ? -$gain : $gain;

            $tailleCercle = $absGain / 15;

            $tailleMin = 7;
            $tailleMax = 30;

            if ($tailleCercle < $tailleMin)
                $tailleCercle = $tailleMin;
            else if ($tailleCercle > $tailleMax)
                $tailleCercle = $tailleMax;

            $tailleCercle = round($tailleCercle, 0);

            $couleurPoint = $gain > 0 ? $this->vert : $this->rouge;

            imagefilledellipse($this->image, $x, $y, $tailleCercle, $tailleCercle, $couleurPoint);
        }

        //Affichage de l'image
        imagestring($this->image, 5, 0, 0, $debugMessage, $this->blanc);

        imagepng($this->image);
        imagedestroy($this->image);
    }

    public $paddingX = 10;
    public $paddingY = 0;

    public function DessinerLigneHorizon($prix, $texte,$taillePolice, $couleur, $taillePointille,$distanceEntreLigne, $positionTexte, ?DateTime $TexteDate)
    {
        $xDebut = 0;
        $xFin = $this->largeur;

        $y = $this->CalculerYValeur($prix);

        $this->DessinerLignePointiller($xDebut,$xFin,$y,$taillePointille,$distanceEntreLigne,$couleur);

        if ($texte != "") {
            $xTexte = ($xFin - $xDebut) / 2;
            if ($TexteDate != null){
                $xTexte = $this->CalculerXDate($TexteDate);
            }

            $xTexte = $xTexte - $taillePolice * (strlen($texte) - 1) / 4;
            $yTexte = $y;

            if ($positionTexte == "centre") {
                $yTexte = $y + $taillePolice / 2;
            } else if ($positionTexte == "bas"){
                $yTexte = $y + $taillePolice * 2.5;
            } else if ($positionTexte == "haut"){
                $yTexte = $y - $taillePolice;
            }

            imagettftext($this->image, $taillePolice, 0, $xTexte, $yTexte, $couleur, '..\src\Oswald-Bold.ttf', $texte);
        }
    }

    public function DessinerLignePointiller ($xDebut,$xfin,$y,$taillePointille, $distanceEntreLigne, $couleur){

        $xActuelle = $xDebut;
        $xProchain = $xDebut + $taillePointille;

        if($xProchain > $xfin) {
            $xProchain = $xfin;
        }

        while($xActuelle < $xfin){
            imageline($this->image, $xActuelle, $y, $xProchain, $y, $couleur);

            $xActuelle = $xProchain + $distanceEntreLigne;
            $xProchain = $xActuelle + $taillePointille;

            if($xProchain > $xfin) {
                $xProchain = $xfin;
            }   
        }
    }

    public function CalculerXDate($date)
    {
        $tickDate = $date->getTimeStamp() - $this->plusPetiteDateTick;

        if ($tickDate < -1) {
            return 0;
        }

        $largeur = $this->largeur - $this->paddingX * 2;

        $tickParPixelLargeur = ($this->plusGrandeDateTick - $this->plusPetiteDateTick) / $largeur;

        return $tickDate / $tickParPixelLargeur + $this->paddingX;
    }

    public function CalculerYValeur($prix)
    {
        $hauteur = $this->hauteur - $this->paddingY * 2;

        $prixOrdonner = $prix - $this->plusPetiteValeurMarger;

        $pixelParPrix = ($this->plusGrosseValeurMarger - $this->plusPetiteValeurMarger) / $hauteur;

        return $hauteur - (($prixOrdonner / $pixelParPrix) + $this->paddingY);
    }
}

class DateValeur
{
    public DateTime $date;

    public float $valeur;

    public float $gain;

    public function __construct(DateTime $date, float $valeur, float $gain)
    {
        $this->date = $date;
        $this->valeur = $valeur;
        $this->gain = $gain;
    }
}