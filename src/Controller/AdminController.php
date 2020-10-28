<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{


    /**
     * @Route("/creercategorie", name="creercategorie")
     */
    public function creercategorie(EntityManagerInterface $manager, Request $request)
    {


        if ($request->isMethod('POST')) {
            $categorie = new Categorie();
            $categorie->setNom($request->request->get('nom'));
            $manager->persist($categorie);
            $manager->flush();

            $this->addFlash("success", "La catégorie a bien été créée");
        }

        return $this->render("annonce/creercategorie.html.twig");
    }

    /**
     * @Route("/listeutilisateurs", name="listeutilisateurs")
     */
    public function listeutilisateurs(UtilisateurRepository $repository)
    {
        $utilisateurs = $repository->findAll();

        return $this->render("security/listeutilisateurs.html.twig",
            [
                'utilisateurs' => $utilisateurs
            ]);
    }

    /**
     * @Route("/supprimerutilisateur/{id}", name="supprimerutilisateur")
     */
    public function supprimerutilisateur($id, UtilisateurRepository $repository, EntityManagerInterface $manager)
    {
        $utilisateur = $repository->find($id);
        $manager->remove($utilisateur);
        $manager->flush();
        return $this->redirectToRoute('listeutilisateurs');
    }

    /**
     * @Route("/contact/{id}", name="contact")
     */
    public function contacter($id, UtilisateurRepository $repository, CategorieRepository $categorieRepository)
    {
        $annonceur = $repository->find($id);

        $categories = $categorieRepository->findAll();

        return $this->render('annonce/annonce-contacter.html.twig', [
            "categories" => $categories,
            "annonceur" => $annonceur
        ]);
    }
}
