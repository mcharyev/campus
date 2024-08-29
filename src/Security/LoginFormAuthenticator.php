<?php

// src/Security/LoginFormAuthenticator.php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Psr\Log\LoggerInterface;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator {

    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $domainUser;
    private $logger;
    private $systemEventManager;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager,
            UserPasswordEncoderInterface $passwordEncoder, LoggerInterface $logger, SystemEventManager $systemEventManager) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->domainUser = false;
        $this->logger = $logger;
        $this->systemEventManager = $systemEventManager;
    }

    public function supports(Request $request) {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function getCredentials(Request $request) {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
                //'logintype' => $request->request->get('logintype')
        ];
        $request->getSession()->set(
                Security::LAST_USERNAME,
                $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

//        //if ($credentials['logintype'] == "domain") {
        //$this->logger->debug('CAMPUS: Checking local user!');
        $localUser = $this->getLocalUser($credentials);

        //echo "This should be printed";

        if ($localUser[0]) {
            //$this->logger->debug('CAMPUS: Local user returned YES');
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_LOGIN, EntityTypeEnum::ENTITY_USER, $localUser[1]->getId(), 0, 0, 'local user');
            return $localUser[1];
        } else {
            //$this->logger->debug('CAMPUS: Local user returned NO');
            //$this->logger->debug('CAMPUS: Checking domain user');
            $domainUser = $this->getDomainUser($credentials);
            if ($domainUser[0]) {
                //$this->logger->debug('CAMPUS: Domain user returned YES');
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_LOGIN, EntityTypeEnum::ENTITY_USER, $domainUser[1]->getId(), 0, 0, 'domain user');
                return $domainUser[1];
            } else {
                //$this->logger->debug('CAMPUS: Domain user returned NO');
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_LOGINFAIL, 0, 0, 0, 0, $credentials['username']);
                return null;
            }
        }

        //return $user;
    }

    private function getDomainUser($credentials) {
        $ldap = ldap_connect($_SERVER['APP_AD_SERVER']);
        $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $credentials['username'];
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        if (strlen($credentials['username']) == 0 || strlen($credentials['password']) == 0) {
            $bind = 0;
        } else {
            $bind = @ldap_bind($ldap, $ldaprdn, $credentials['password']);
        }

        if ($bind) {
            $this->logger->debug('Domain binding returned TRUE!');
            $this->domainUser = true;
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);
            if (!$user) {
                $this->logger->debug('Domain binding returned TRUE and User NOT found!');
                // fail authentication with a custom errordd
                //throw new CustomUserMessageAuthenticationException('Username could not be found.');
                return [false, null];
            } else {
                $this->logger->debug('Domain binding returned TRUE and User FOUND!');
                return [true, $user];
            }
        } else {
            $this->logger->debug('Domain binding returned FALSE!');
            return [false, null];
        }
    }

    private function getLocalUser($credentials) {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(
                ['username' => $credentials['username']]);
        if (!$user) {
            // fail authentication with a custom error
            //throw new CustomUserMessageAuthenticationException('Local username could not be found.');
            return [false, null];
        } else {
            if ($this->checkCredentials($credentials, $user)) {
                return [true, $user];
            } else {
                return [false, $user];
            }
        }
    }

    public function checkCredentials($credentials, UserInterface $user) {
        if ($this->domainUser) {
            return true;
        } else {
            return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_index'));

        // For example : return new RedirectResponse($this->router->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside ' . __FILE__);
    }

    protected function getLoginUrl() {
        return $this->router->generate('app_login');
    }

}
