<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Service\SystemInfoManager;

class SecurityController extends AbstractController {

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, SystemInfoManager $systemInfoManager): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $year1 = $systemInfoManager->getFirstSemesterYear();
        $year2 = $systemInfoManager->getSecondSemesterYear();

        return $this->render('security/login.html.twig', [
                    'last_username' => $lastUsername,
                    'error' => $error,
                    'academic_year' => $year1 . "-" . $year2,
        ]);
    }

    /**
     * @Route("/login/passwordRecovery", name="app_login_check")
     */
    public function loginPasswordRecovery() {
        return new Response("checking");
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(SystemEventManager $systemEventManager) {
        $systemEventManager->create(null, SystemEventTypeEnum::EVENT_LOGOUT, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), 0, 0, '');
    }

    /**
     * @Route("/logout_message", name="logout_message")
     */
    public function logoutMessage(SystemEventManager $systemEventManager) {
        $this->addFlash('success', "You signed out!");
        return $this->redirectToRoute('app_login');
    }

}
