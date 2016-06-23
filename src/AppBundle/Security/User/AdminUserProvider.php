<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminUserProvider implements UserProviderInterface
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
            ->select('a')
            ->from('AppBundle:Admin', 'a')
            ->where('a.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        var_dump($user);
//        die('---');
        if($user != null){

            $adminUser = new AdminUser($user->getEmail(), $user->getPassword());
            $adminUser->setId($user->getId())
                ;

            return $adminUser;
        }

        throw new UsernameNotFoundException(
            sprintf('Username %s does not exist', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof AdminUser){
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\AdminUser';
    }
}

?>