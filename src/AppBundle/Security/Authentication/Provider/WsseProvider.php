<?php

namespace AppBundle\Security\Authentication\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use AppBundle\Security\Authentication\Token\WsseUserToken;

class WsseProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cachePool;

    public function __construct(UserProviderInterface $userProvider, CacheItemPoolInterface $cachePool)
    {
        $this->userProvider = $userProvider;
        $this->cachePool = $cachePool;
    }

    public function authenticate(TokenInterface $token)
    {

        //switch
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $isValid = $this->validateDigest($token->digest, $token->nonce, $token->created, $user->getPassword());

        if ($user && $isValid) {
            $authenticatedToken = new WsseUserToken($user->getRoles());

            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The WSSE authentication failed.');
    }

    /**
     * This function is specific to Wsse authentication and is only used to help this example
     *
     * For more information specific to the logic here, see
     * https://github.com/symfony/symfony-docs/pull/3134#issuecomment-27699129
     *
     * @param $digest
     * @param $nonce
     * @param $created
     * @param $secret
     * @return bool
     */
    protected function validateDigest($digest, $nonce, $created, $secret)
    {
        // Check created time is not in the future
        if (strtotime($created) > time()) {
            return false;
        }

        $salem = time() - strtotime($created) ."<br>" . time();

        // Expire timestamp after 5 minutes
        if (time() - strtotime($created) > 300) {
            return false;
        }

        // Try to fetch the cache item from pool
        $cacheItem = $this->cachePool->getItem(md5($nonce));

        // Validate that the nonce is *not* in cache
        // if it is, this could be a replay attack
        if ($cacheItem->isHit()) {
            throw new NonceExpiredException('Previously used nonce detected');
        }

        // Store the item in cache for 5 minutes
        $cacheItem->set(null)->expiresAfter(300);
        $this->cachePool->save($cacheItem);

      
        // Validate Secret
        $expected = base64_encode(sha1(base64_encode($nonce).$created.$secret));
        //echo json_encode(['expected'=>$expected, 'orignal'=>$digest]);die();
        return hash_equals($expected, $digest);
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseUserToken;
    }
}