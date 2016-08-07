<?php
namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class CompanyUser implements EquatableInterface, AdvancedUserInterface
{

    private $email;
    private $password;
    private $id;
    private $roles;
    private $salt;
    private $url;
    private $logo;
    private $isDefault;
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
     * CompanyUser constructor.
     * @param $email
     * @param $password
     * @param $name
     * @param $url
     * @param $logo
     * @param $isActive
     * @param string $salt
     * @param integer $isDefault
     * @param array $roles
     */
    public function __construct($email, $password,$name,$url,$logo,$isActive,$salt="", $isDefault=0, $roles=array('ROLE_COMPANY', 'ROLE_API'))
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->salt = $salt;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->url = $url;
        $this->logo = $logo;
        $this->isDefault = $isDefault;

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
    public function getUrl()
    {
        return $this->url;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $id
     * @return CompanyUser
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        // TODO: Implement isAccountNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        // TODO: Implement isAccountNonLocked() method.
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        // TODO: Implement isCredentialsNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isActive;
        // TODO: Implement isEnabled() method.
    }
}

?>