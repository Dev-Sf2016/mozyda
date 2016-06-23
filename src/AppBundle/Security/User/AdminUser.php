<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\Admin;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class AdminUser implements EquatableInterface, UserInterface
{

    private $email;
    private $password;
    private $id;
    private $roles;
    private $salt;
    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     *
     * @return bool
     */

    public function __construct($email, $password,$salt="",$roles=array('ROLE_ADMIN'))
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->salt = $salt;

    }
    public function isEqualTo(UserInterface $user)
    {
//        if(!$user instanceof EmployeeUserUser){
//            return false;
//        }
        if($this->email !== $user->getUsername()){
            return false;
        }
//
        if($this->password !== $user->getPassword()){
            return false;
        }
//
//        if($this->salt !== $user->getSalt()){
//            return false;
//        }

        return true;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return AdminUser
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}

?>