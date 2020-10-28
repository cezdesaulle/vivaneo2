<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use App\Repository\UtilisateurRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function Sodium\add;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

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
    public function inscription(UserAuthenticator $userAuthenticator,GuardAuthenticatorHandler $handler,Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {

        if ($request->isMethod('POST')){

            $utilisateur = new Utilisateur();


            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setEmail($request->request->get('_username'));
            $encode=$encoder->encodePassword($utilisateur,
                $request->request->get('_password'));
            $utilisateur->setPassword($encode);
            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', 'Votre compte a bien été créé');
            return $handler->authenticateUserAndHandleSuccess(
                $utilisateur,
                $request,
                $userAuthenticator,
                'main'
            );

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
     * @Route("/mesannonces", name="mesannonces")
     */
    public function mesannonces(AnnonceRepository $annonceRepository)
    {
        $annonceur=$this->getUser();
        $annonces=$annonceRepository->findBy(['annonceur'=>$annonceur]);

        return $this->render('utilisateur/tableaudebord-mesannonces.html.twig',
        [
           'annonces'=>$annonces
        ]
        );
    }

    /**
     * @Route("/modifannonce/{id}", name="modifannonce")
     */
    public function modifannonce(Request $request,$id,AnnonceRepository $annonceRepository, EntityManagerInterface $manager,CategorieRepository $repository)
    {
        $annonce = $annonceRepository->find($id);
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('categorie');
            $categorie = $repository->findOneBy(['nom' => $nom]);
            $annonceur = $this->getUser();
            $annonce->setAnnonceur($annonceur);
            $annonce->setCategorie($categorie);
            $annonce->setTitre($request->request->get('titre'));
            $annonce->setContenu($request->request->get('contenu'));

            $manager->persist($annonce);

            $manager->flush();
            $this->addFlash('success', 'Votre annonce a bien été modifiée');
            return $this->redirectToRoute("tableaudebord");
        }
        $categories=$repository->findAll();

        return $this->render("annonce/annonce-publier.html.twig",[
            'annonce'=>$annonce,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/supprimerannonce/{id}", name="supprimerannonce")
     */
    public function supprimerannonce($id,AnnonceRepository $annonceRepository, EntityManagerInterface $manager)
    {
        $annonce=$annonceRepository->find($id);
        $manager->remove($annonce);
        $manager->flush();
        $this->addFlash("success", "Annonce supprimée!!");
        $annonceur=$this->getUser();
        $annonces=$annonceRepository->findBy(['annonceur'=>$annonceur]);

        return $this->render("utilisateur/tableaudebord-mesannonces.html.twig",
            [
                'annonces'=>$annonces
            ]);
    }


    /**
     * @Route("modifmail", name="modifmail")
     */
    public function mailmodif(UserAuthenticator $authenticator,UserInterface $user,Request $request, EntityManagerInterface $manager)
    {
        $utilisateur=$this->getUser();
        $mdp=$request->request->all();


        if ($authenticator->checkCredentials($mdp,$user)){

        $utilisateur->setEmail($request->request->get('email'));
        $manager->persist($utilisateur);
        $manager->flush();
        $this->addFlash('success', 'Email modifié avec succès');
        }else{
            $this->addFlash('error', "Le mot de passe n'est pas valide");
        }
        return $this->redirectToRoute('tableaudebord');
    }

    /**
     * @Route("/tableaumail", name="tableaumail")
     */
    public function tableaumail()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-email.html.twig",[
            "utilisateur"=>$utilisateur
        ]);
    }


    /**
     * @Route("/modifidentite", name="modifidentite")
     */
    public function identitemodif(UtilisateurRepository $repository, Request $request, EntityManagerInterface $manager)
    {
        $utilisateur=$this->getUser();


        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $manager->persist($utilisateur);
        $manager->flush();
        $this->addFlash('success', 'Nom et Prénom modifiés avec succès');
        return $this->redirectToRoute('tableaudebord');

    }

    /**
     * @Route("/tableauidentite", name="tableauidentite")
     */
    public function tableauidentite()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-identite.html.twig",[
            "utilisateur"=>$utilisateur
        ]);

    }

    /**
     * @Route("/modifpassword", name="modifpassword")
     */
    public function passwordmodif(UserAuthenticator $authenticator,UserInterface $user,UserPasswordEncoderInterface $encoder, Request $request, EntityManagerInterface $manager)
    {

        $utilisateur=$this->getUser();
        $mdp=$request->request->all();


        if ($authenticator->checkCredentials($mdp,$user)) {
            if ($request->request->get('mdp') == $request->request->get('remdp')){
            $encode = $encoder->encodePassword($utilisateur,
                $request->request->get('mdp'));
            $utilisateur->setPassword($encode);
            $manager->persist($utilisateur);
            $manager->flush();
            $this->addFlash('success', 'Mot de passe modifié avec succès');
            }else{
                $this->addFlash('error', "le Mot de passe répété n'est pas identique");
            }
        }else{
            $this->addFlash('error', "Votre ancien mot de passe n'est pas valide");
        }
        return $this->redirectToRoute('tableaudebord');
    }

    /**
     * @Route("/tableaupassword", name="tableaupassword")
     */
    public function tableaupassword()
    {
        $utilisateur= $this->getUser();

        return $this->render("utilisateur/tableaudebord-motdepasse.html.twig",[
            "utilisateur"=>$utilisateur
        ]);
    }



    /**
     * @Route("/motdepasseoublie", name="motdepasseoublie")
     */
    public function motdepasseoublie(Request $request, UtilisateurRepository $repository, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $mail=$request->request->get('email');
        $utilisateur=$repository->findOneBy(['email' => $mail]);


        if ($request->isMethod('POST')){

        // Si l'utilisateur n'existe pas
        if ($utilisateur === null) {
            // On envoie une alerte disant que l'adresse e-mail est inconnue
            $this->addFlash('danger', 'Cette adresse e-mail est inconnue');

            // On retourne sur la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // On génère un token
        $token = $tokenGenerator->generateToken();

        // On essaie d'écrire le token en base de données
        try{
            $utilisateur->setResetToken($token);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($utilisateur);
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }

        // On génère l'URL de réinitialisation de mot de passe
        $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

        // On génère l'e-mail
        $message = (new Email())
            ->from('cezdesaulle@gmail.com')
            ->to($mail)
            ->html(
                "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site vivaneo. Veuillez cliquer sur le lien suivant : " . $url,
                'text/html'
            );

        // On envoie l'e-mail
        $mailer->send($message);

        // On crée le message flash de confirmation
        $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé !');

        // On redirige vers la page de login
        return $this->redirectToRoute('app_login');
        }


        return $this->render("utilisateur/motdepasseperdu.html.twig");
    }

    /**
     * @Route("/reset_pass/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['reset_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setResetToken(null);

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

            // On stocke
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }

    }

}
