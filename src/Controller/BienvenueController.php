<?php

namespace App\Controller;

use App\Back\Graphique\GenGraphique;
use App\Back\Calcul;
use App\Back\LectureTransactionEtoro;
use App\Entity\Vente;
use App\Repository\VenteRepository;
use DateTime;
use Proxies\__CG__\App\Entity\Cours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\MISC\PHPHelper;
use App\Entity\Utilisateur;

class BienvenueController extends AbstractController
{
    #[Route('/bvn', name: 'bienvenue', methods: ['GET'])]
    public function index(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $utilisateur = $session->get('utilisateur', );

        $repository = $doctrine->getRepository(Vente::class);

        $listeVente = $repository->TrouverVente($utilisateur);

        $sommeTotale = Calcul::getGpTotale($listeVente);

        //Creation listeVente js 
        $listeVenteCopie =  array_merge([], $listeVente);

        usort($listeVenteCopie, function (Vente $a, Vente $b) {
            return $a->getDateVente() <=> $b->getDateVente();
        });

        $premiereTransaction = $listeVenteCopie[0];

        $dateDebut = clone $premiereTransaction->getDateVente();

        $dateDebut->modify('-1 day');

        $listeVenteJS[] = [
            'idTransaction'=>-1,
            'ordre'=>0,
            'gp'=>0,
            'gpTotale'=>0,
            'date'=>$dateDebut->format(DateTime::ATOM)
        ];

        $gpActu = 0;
        $ordre = 1;

        foreach($listeVenteCopie as $vente){
            $gpActu = $gpActu + $vente->getGP();
            $listeVenteJS[] = [
                'idTransaction'=>$vente->getId(),
                'ordre'=>$ordre,
                'gp' => $vente->getGP(),
                'gpTotale'=>$gpActu,
                'date' => $vente->getDateVente()->format(DateTime::ATOM),
            ];
            $ordre++;
        }
        //Fin creation liste vente js

        $sousMessage = $session->get('bvn_message');
        $sousMessageClasse = $session->get('bvn_message_couleur');

        $modeDate = $session->get('modeDate'); //REEL, IDENTIQUE, DISTINGUER

        $sommeTotaleAvecFrais = $sommeTotale - $utilisateur->getFrais();

        return $this->render('Bienvenue.html.twig', [
            'nom' => $utilisateur->getNom(),
            'sommeTotale' => $sommeTotale,
            'sousMessage' => $sousMessage,
            'sousMessageClasse' => $sousMessageClasse,
            'listeVente' => $listeVente,
            'listeVenteJS' => $listeVenteJS,
            'fraisSup' => $utilisateur->getFrais(),
            'sommeTotaleAvecFrais' => $sommeTotaleAvecFrais,
            'modeDate' => $modeDate
        ]);
    }

    #[Route('/importerTransactionEtoro', name: 'importerTransactionEtoro', methods: ['POST'])]
    public function ImporterTransactionEtoro(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $utilisateur = $session->get('utilisateur');

        $fichier = $request->files->get('fichier_etoro');

        if ($fichier) {
            $cheminTemporaire = $fichier->getPathname();

            // Passe le chemin temporaire au lecteur de fichiers
            LectureTransactionEtoro::Lire($cheminTemporaire, $utilisateur, $session, $doctrine);
        }

        return $this->redirectToRoute('bienvenue');
    }

    #[Route('/ReninitMessageBvn', name: 'ReinitMessageBvn', methods: ['GET'])]
    public function ReinitMessageBvn(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $session->set('bvn_message', '');

        return $this->redirectToRoute('bienvenue');
    }
    
    #[Route('/image_cours/{id}', name: 'imageCours')]
    public function displayImage(int $id,SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Cours::class);

        $cours  = $repository->getFromId($id);

        if($cours == null){
            return new Response();
        }

        $blob = $cours->getImage();
        
        if($blob == null){
            return new Response();
        }

        $imageData = stream_get_contents($cours->getImage());

        if ($imageData === false) {
            throw new \RuntimeException('Erreur de lecture du BLOB');
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', 'image/jpeg'); // ou 'image/png'

        return $response;
    }
}