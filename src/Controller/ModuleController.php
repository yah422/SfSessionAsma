<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Session;
use App\Form\ModuleType;
use App\Form\SessionType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController
{
    #[Route('/module', name: 'app_module')]
    public function index(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findBy([], ["nom" => "ASC"]);
        return $this->render('module/index.html.twig', [
            'modules' => $modules,
        ]);
    }

    #[Route('/module/add', name: 'add_module')]
    public function add(Request $request, EntityManagerInterface $entityManager,Security $security): Response
    {

          // Vérification du rôle 'ROLE_ADMIN'
          if (!$security->isGranted('ROLE_ADMIN')) {
            // Rediriger vers une page d'erreur si l'utilisateur n'a pas le rôle 'ROLE_ADMIN'
            return $this->render('module/errorPage.html.twig');     
        }

        $module = new Module(); // Créez un nouvel objet Module ici
        $form = $this->createForm(ModuleType::class, $module); // Utilisation de ModuleType
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($module); // l'objet Module
            
            $entityManager->flush();
    
            return $this->redirectToRoute('app_module');
        }
    
        return $this->render('module/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/module/{id}/edit", name:"module_edit")]
    public function edit(Security $security, Request $request, Module $module, EntityManagerInterface $entityManager, int $id): Response
    {
         // Vérification du rôle 'ROLE_ADMIN'
         if (!$security->isGranted('ROLE_ADMIN')) {
            // Rediriger vers une page d'erreur si l'utilisateur n'a pas le rôle 'ROLE_ADMIN'
            return $this->render('stagiaire/errorPage.html.twig');     
            }

        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_module', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('module/edit.html.twig', [
            'module' => $module,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/module/supprimer/{id}', name: 'delete_module', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Module $module, EntityManagerInterface $entityManager,CsrfTokenManagerInterface $csrfTokenManager,int $id): Response
    {

        if (!$module) {
            throw $this->createNotFoundException('No module found for id ' . $id);
        }
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete_module', $request->request->get('_token'))) {
            
            $entityManager->remove($module);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_module');

        }
        
        // Si le token est invalide, lever une exception
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }


    #[Route('/module/{id}', name: 'show_module')]
    public function show(Module $module): Response
    {
        return $this->render('module/show.html.twig', [
            'module' => $module,

        ]);

    }
}
