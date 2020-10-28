<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\Utilisateur;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use App\Repository\UtilisateurRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class AnnonceController extends AbstractController
{

    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil()
    {
        return $this->render('annonce/index.html.twig');
    }


    /**
     * @Route("/publier", name="publier")
     */
    public function publier(AnnonceRepository $annonceRepository, UtilisateurRepository $utilisateurRepository, UserAuthenticator $userAuthenticator, GuardAuthenticatorHandler $handler, CategorieRepository $repository, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {

        if ($request->isMethod('POST')) {

            if (!empty($request->request->get('nom'))) {
                $utilisateur = new Utilisateur();

                $utilisateur->setNom($request->request->get('nom'));
                $utilisateur->setPrenom($request->request->get('prenom'));
                $utilisateur->setEmail($request->request->get('_username'));
                $encode = $encoder->encodePassword($utilisateur,
                    $request->request->get('_password'));
                $utilisateur->setPassword($encode);
                $manager->persist($utilisateur);
                $manager->flush();
                $this->addFlash('success', 'Votre compte a bien été créé');


                $annonce = new Annonce();
                $nom = $request->request->get('categorie');
                $categorie = $repository->findOneBy(['nom' => $nom]);

                $id = $utilisateur->getId();
                $annonceur = $utilisateurRepository->find($id);

                $annonce->setAnnonceur($annonceur);
                $annonce->setCategorie($categorie);
                $annonce->setTitre($request->request->get('titre'));
                $annonce->setContenu($request->request->get('contenu'));

                $manager->persist($annonce);

                $manager->flush();
                $this->addFlash('success', 'Votre annonce a bien été publiée');
                return $handler->authenticateUserAndHandleSuccess(
                    $utilisateur,
                    $request,
                    $userAuthenticator,
                    'main'
                );

            } else {
                $annonce = new Annonce();
                $nom = $request->request->get('categorie');
                $categorie = $repository->findOneBy(['nom' => $nom]);
                $annonceur = $this->getUser();

                $annonce->setAnnonceur($annonceur);
                $annonce->setCategorie($categorie);
                $annonce->setTitre($request->request->get('titre'));
                $annonce->setContenu($request->request->get('contenu'));

                $manager->persist($annonce);
                $manager->flush();
                $this->addFlash('success', 'Votre annonce a bien été publiée');
                return $this->redirectToRoute('annonceliste');

            }
        }

        $categories = $repository->findAll();

        return $this->render('annonce/annonce-publier.html.twig', ['categories' => $categories]);

    }



    /**
     * @Route("/annonceliste/{id}",defaults={"id": ""}, name="annonceliste")
     */
    public function annonce_liste($id, AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository)
    {
        if ($id == "") {
            $annonces = $annonceRepository->findAll();
        } else {
            $annonces = $annonceRepository->findBy(['categorie' => $id]);
        }

        $categories = $categorieRepository->findAll();
        return $this->render('annonce/annonce-liste.html.twig', [
            "annonces" => $annonces,
            'categories' => $categories

        ]);
    }


    /**
     * @Route("/annonce/{id}", name="annonce")
     */
    public function annonce($id, AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository)
    {
        $annonce = $annonceRepository->find($id);
        $categories = $categorieRepository->findAll();

        return $this->render('annonce/annonce.html.twig', [
            'annonce' => $annonce,
            'categories' => $categories
        ]);
    }


    /**
     * @Route("/contacter/{id}", name="contacter")
     */
    public function contacter($id, AnnonceRepository $repository, CategorieRepository $categorieRepository)
    {
        $categories=$categorieRepository->findAll();
        $annonce = $repository->find($id);
        $annonceur = $annonce->getAnnonceur();



        return $this->render('annonce/annonce-contacter.html.twig', [

            "categories" => $categories,
            "annonceur" => $annonceur
        ]);
    }

    /**
     * @Route("/envoimail", name="envoimail")
     * @param MailerInterface $mailer
     *
     */
    public function envoimail(MailerInterface $mailer, Request $request)
    {
        $destinataire = $request->request->get('email');
        $destinateur = $request->request->get('annonceur');
        $message = $request->request->get('message');

        $email = (new Email())
            ->from($destinataire)
            ->to($destinateur)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text($message)
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return new Response(
            'Email was sent'
        );

    }




}
