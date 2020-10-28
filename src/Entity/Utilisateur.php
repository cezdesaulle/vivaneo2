<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 * @UniqueEntity(fields={"_username"},message="L'adresse mail est déjà utilisée")
 */
class Utilisateur implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(message="l'email n'est pas valide")
     * @Assert\Email(mode="strict", message="Le format de l'email est incorrect")
     * @Assert\Email(checkMX=true, message="Aucun serveur mail n'a été trouvé pour ce domaine")
     */
    private $email;

    /**
     *@ORM\Column(type="json")
     */
    private $roles = 'ROLE_USER';

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Le mot de passe est obligatoire")
     * @Assert\Regex("/^[a-zA-Z0-9]{6,20}$/", message="Mot de passe non conforme")
     */
    private $password;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookID;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookAccessToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $googleID;

    /**
     * @return mixed
     */
    public function getGoogleID()
    {
        return $this->googleID;
    }

    /**
     * @param mixed $googleID
     */
    public function setGoogleID($googleID): void
    {
        $this->googleID = $googleID;
    }

    /**
     * @return mixed
     */
    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    /**
     * @param mixed $googleAccessToken
     */
    public function setGoogleAccessToken($googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $googleAccessToken;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Length(max="20", maxMessage="Le nom ne peut contenir plus de 20 caractères.")
     * @Assert\Length(min="3", minMessage="Le nom ne peut contenir moins de 3 caractères.")
     * @Assert\NotBlank(message="Veuillez indiquer votre prénom et nom")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Length(max="20", maxMessage="Le nom ne peut contenir plus de 20 caractères.")
     * @Assert\Length(min="3", minMessage="Le nom ne peut contenir moins de 3 caractères.")
     * @Assert\NotBlank(message="Veuillez indiquer votre prénom et nom")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_token;



    /**
     * @return mixed
     */
    public function getFacebookID()
    {
        return $this->facebookID;
    }



    /**
     * @param mixed $facebookID
     */
    public function setFacebookID($facebookID): void
    {
        $this->facebookID = $facebookID;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param mixed $facebookAccessToken
     */
    public function setFacebookAccessToken($facebookAccessToken): void
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return [$this->roles];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }
}
