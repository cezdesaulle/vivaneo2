<?php

namespace App\Security;

use App\Entity\Utilisateur;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\UnsupportedException;

class MyEntityUserProvider extends EntityUserProvider implements AccountConnectorInterface {

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $ressourceOwnerName = $response->getResourceOwner()->getName();
        if (!isset($this->properties[$ressourceOwnerName])){
            throw new \RuntimeException(sprintf("Aucune information sur le compte", $ressourceOwnerName));
        }


        $serviceName = $response->getResourceOwner()->getName();
        $setterId= 'set'.ucfirst($serviceName).'ID';
        $setterAccessToken= 'set'.ucfirst($serviceName).'AccessToken';

        $username = $response->getUsername();
        if (null === $user = $this->findUser(array($this->properties[$ressourceOwnerName] => $username))){

        $user = new Utilisateur();
        $user->setEmail($response->getEmail());
        $user->setNom($response->getLastName());
        $user->setPrenom($response->getFirstName());
        $user->$setterId($username);
        $user->$setterAccessToken($response->getAccessToken());

        $this->em->persist($user);
        $this->em->flush();

            return $user;

        }

        $user->setFacebookAccessToken($response->getAccessToken());
        return $user;
}


    /**
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof Utilisateur){
            throw new UnsupportedException("EXpected aninstance of App\Model\Username");
        }
        $property= $this->getProperty($response);
        $username= $response->getUsername();

        if (null !== $previousUser = $this->registry->getRepository(Utilisateur::class)->findOneBy(array($property=>$username))){

            $this->disconnect($previousUser, $response);
        }

        $serviceName = $response->getResourceOwner()->getName();
        $setter= 'set'.ucfirst($serviceName).'AccessToken';
        $user->$setter($response->getAccessToken());
        $this->updateUser($user, $response);


    }

    /**
     * @param UserResponseInterface $response
     * @return mixed|string
     */
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        if (!isset($this->properties[$resourceOwnerName])){
            throw new UnsupportedException("ENo property defined for Entity for resource");
        }
        return $this->properties[$resourceOwnerName];
    }

    public function disconnect(UserInterface $user, UserResponseInterface $response)
    {
        $property= $this->getProperty($response);
        $accessor= PropertyAccess::createPropertyAccessor();

        $accessor->setValue($user, $property, null);
        $this->updateUser($user, $response);
    }

    private function updateUser(UserInterface $user, UserResponseInterface $response)
    {
        $user->setEmail($response->getEmail());
        $this->em->persist($user);
        $this->em->flush();

    }
}