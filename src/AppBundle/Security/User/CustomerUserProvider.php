<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\Customer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerUserProvider implements UserProviderInterface
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function loadUserByUsername($username)
    {
        echo ('--loaduser');
        /**
         * @var \AppBundle\Entity\Customer
         */
        $user = $this->em->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Customer', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        var_dump($user);
//        die('---');
        if($user != null){

            $customerUser = new CustomerUser($user->getEmail(), $user->getPassword(), $user->getName(), $user->getLoyalityId(), $user->getCity(), $user->getNationality(), $user->getIsActive());
            $customerUser->setId($user->getId())
                ;

            return $customerUser;
        }

        throw new UsernameNotFoundException(
            sprintf('Username %s does not exist', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof CustomerUser){
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\CustomerUser';
    }
}

?>