<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\Mapping\Id;
use App\Controller\FlashyNotifier;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findBy([],["nom" => "ASC"]);
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Méthode pour ajouter une catégorie
    #[Route('/categorie/ajouter', name: 'add_categorie')]
    #[IsGranted('ROLE_ADMIN')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de Categorie
        $categorie = new Categorie();

        // Création du formulaire
        $form = $this->createForm(CategorieType::class, $categorie);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde de la catégorie en base de données
            $entityManager->persist($categorie);
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('success', 'Catégorie ajoutée avec succès !');

            // Redirection vers la page de liste des catégories (à ajuster si nécessaire)
            return $this->redirectToRoute('app_categorie');
        }

        // Rendu de la vue avec le formulaire
        return $this->render('categorie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/categorie/supprimer/{id}', name: 'delete_categorie', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, CsrfTokenManagerInterface $csrfTokenManager, Categorie $categorie, EntityManagerInterface $entityManager,int $id): Response
    {
        if (!$categorie) {
            throw $this->createNotFoundException('No categorie found for id ' . $id);
        }
     
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete_categorie', $request->request->get('_token'))) {
            // Si valide, on peut supprimer la categorie
            $entityManager->remove($categorie);
            $entityManager->flush();

            // Redirection après suppression
            return $this->redirectToRoute('app_categorie');
        }
     
        // Si le token est invalide, lever une exception
        throw $this->createAccessDeniedException('Token CSRF invalide.');
        
    }

    #[Route('/categorie/{id}', name: 'show_categorie')]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie

        ]);

    }
}
