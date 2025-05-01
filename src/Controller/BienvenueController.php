<?php

namespace App\Controller;

use App\Calcul\Calcul;
use App\Entity\Vente;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\MISC\PHPHelper;
use App\Entity\Utilisateur;

class BienvenueController extends AbstractController {

    #[Route('/bvn', name: 'bienvenue', methods: ['GET'])]
    public function index (Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {   
        $utilisateur = $session->get('utilisateur',);

        $repository = $doctrine->getRepository(Vente::class);

        $listeVente = $repository->TrouverVente($utilisateur);

        $sommeTotale = Calcul::ObtenirSommeTotale($listeVente);
            
        return $this->render('Bienvenue.html.twig', [
            'nom'=>$utilisateur->getNom(),
            'sommeTotale'=>$sommeTotale
        ]);
    }
}