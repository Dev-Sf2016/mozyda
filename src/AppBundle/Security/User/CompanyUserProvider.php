<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\CompanyDelegate;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class CompanyUserProvider implements UserProviderInterface
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function loadUserByUsername($username)
    {

        $user = $this->em->getRepository('AppBundle:CompanyDelegate')->findByEmail($username);

        if($user != null){

            $company = $user->getCompany();
            
            $companyUser = new CompanyUser(
                $user->getEmail(),
                $user->getPassword(),
                $user->getName(),
                $company->getUrl(),
                $company->getLogo(),
                $company->getIsActive(),
                null,
                $user->getIsDefault());
            $companyUser->setId($user->getId());

            return $companyUser;
        }

        throw new UsernameNotFoundException(
            sprintf('Username %s does not exist', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof CompanyUser){
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\CompanyUser';
    }
}

?>