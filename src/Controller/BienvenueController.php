<?php

namespace App\Controller;

use App\Back\Graphique\GenGraphique;
use App\Back\Calcul;
use App\Back\LectureTransactionEtoro;
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

        $sommeTotale = Calcul::getGpTotale($listeVente);
            
        $sousMessage = $session->get('bvn_message');
        $sousMessageClasse = $session->get('bvn_message_couleur');

        return $this->render('Bienvenue.html.twig', [
            'nom'=>$utilisateur->getNom(),
            'sommeTotale'=>$sommeTotale,
            'sousMessage'=>$sousMessage,
            'sousMessageClasse'=>$sousMessageClasse,
            'listeVente'=>$listeVente
        ]);
    }

    #[Route('/importerTransactionEtoro', name: 'importerTransactionEtoro', methods: ['POST'])]
    public function ImporterTransactionEtoro (Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {   
        $utilisateur = $session->get('utilisateur');

        $fichier = $request->files->get('fichier_etoro');
    
        if ($fichier) {
            $cheminTemporaire = $fichier->getPathname();
    
            // Passe le chemin temporaire au lecteur de fichiers
            LectureTransactionEtoro::Lire($cheminTemporaire, $utilisateur,$session, $doctrine);
        }
        
        return $this->redirectToRoute('bienvenue');
    }

    
    #[Route('/ReninitMessageBvn', name: 'ReinitMessageBvn', methods: ['GET'])]
    public function ReinitMessageBvn (Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {   
        $session->set('bvn_message','');

        return $this->redirectToRoute('bienvenue');
    }

    #[Route('/GrapheGeneral', name: 'GrapheGeneral')]
    public function imageDynamique(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $utilisateur = $session->get('utilisateur',);

        $repository = $doctrine->getRepository(Vente::class);

        $listeVente = $repository->TrouverVente($utilisateur);

        $gen = new GenGraphique();

        $gen->remplirDonnee_vente($listeVente);

        $width = (int) $request->query->get('width',  1000);   // Valeur par défaut : 500
        $height = (int) $request->query->get('height', 1000); // Valeur par défaut : 100
    
        // Capture le rendu de l'image dans un buffer
        ob_start(); // Démarrer le buffer de sortie
        $gen->generer($width,$height,0,0,0,0);
        $imageData = ob_get_clean();

        return new Response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="graphique.png"',
        ]);

    }
}