<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }


        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('utilisateur/connexion.html.twig', ['last_username' => $lastUsername]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/inscription", name="inscription")
     *
     */
    public function inscription(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {


        if ($request->isMethod('POST')){

            $utilisateur = new Utilisateur();


            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setEmail($request->request->get('email'));
            $encode=$encoder->encodePassword($utilisateur,
                $request->request->get('password'));
            $utilisateur->setPassword($encode);
            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', 'Votre compte a bien été créé');


            return $this->redirectToRoute('accueil');

        }

        return $this->render('utilisateur/inscription.html.twig', [
        ]);

    }

    /**
     * @Route("/tableaudebord", name="tableaudebord")
     */
    public function tableaudebord()
    {
        return $this->render('utilisateur/tableaudebord.html.twig');
    }

    /**
     * @Route("modifmail", name="modifmail")
     */
    public function mailmodif(UserPasswordEncoderInterface $encoder,AuthenticationUtils $authenticationUtils,UtilisateurRepository $repository, Request $request, EntityManagerInterface $manager)
    {
        $utilisateur=$repository->find($this->getUser()->getId());

        $user = $this->getUser();

        $hash=$encoder->encodePassword($utilisateur,
            $request->request->get('password'));




        $utilisateur->setEmail($request->request->get('email'));
        $manager->persist($utilisateur);
        $manager->flush();
        $this->addFlash('success', 'Email modifié avec succès');
        return $this->redirectToRoute('tableaudebord');

    }

    /**
     * @Route("tableaumail", name="tableaumail")
     */
    public function tableaumail()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-email.html.twig",[
            "utilisateur"=>$utilisateur
        ]);
    }


    /**
     * @Route("modifidentite", name="modifidentite")
     */
    public function identitemodif(UtilisateurRepository $repository, Request $request, EntityManagerInterface $manager)
    {
        $utilisateur=$repository->find($this->getUser()->getId());



        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $manager->persist($utilisateur);
        $manager->flush();
        $this->addFlash('success', 'Nom et Prénom modifiés avec succès');
        return $this->redirectToRoute('tableaudebord');

    }

    /**
     * @Route("tableauidentite", name="tableauidentite")
     */
    public function tableauidentite()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-identite.html.twig",[
            "utilisateur"=>$utilisateur
        ]);
    }

    /**
     * @Route("modifpassword", name="modifpassword")
     */
    public function passwordmodif( UserPasswordEncoderInterface $encoder,UtilisateurRepository $repository, Request $request, EntityManagerInterface $manager)
    {
        $utilisateur=$repository->find($this->getUser()->getId());



        $encode=$encoder->encodePassword($utilisateur,
            $request->request->get('password'));
        $utilisateur->setPassword($encode);
        $manager->persist($utilisateur);
        $manager->flush();
        $this->addFlash('success', 'Mot de passe modifié avec succès');
        return $this->redirectToRoute('tableaudebord');

    }

    /**
     * @Route("tableaupassword", name="tableaupassword")
     */
    public function tableaupassword()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-motdepasse.html.twig",[
            "utilisateur"=>$utilisateur
        ]);
    }

}
