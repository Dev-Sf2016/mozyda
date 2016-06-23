<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\Company;
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
//        die ('--loaduser');
        /**
         * @var \AppBundle\Entity\Company
         */
        $user = $this->em->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Company', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        var_dump($user);
//        die('---');
        if($user != null){

            $companyUser = new CompanyUser($user->getEmail(), $user->getPassword(), $user->getName(), $user->getUrl(),$user->getLogo(), $user->getIsActive());
            $companyUser->setId($user->getId())
                ;

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