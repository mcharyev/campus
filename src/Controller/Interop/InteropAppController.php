<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Interop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\DBAL\Driver\Connection;
use App\Enum\ClassroomTypeEnum;
use App\Enum\UserTypeEnum;
use App\Entity\Teacher;
use App\Entity\TaughtCourse;
use App\Entity\EnrolledStudent;
use App\Hr\Entity\Employee;
use App\Entity\AlumnusStudent;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

/**
 * Controller used to manage IUHD Campus App.
 *
 */
class InteropAppController extends AbstractController {
    
    private $systemEventManager;

    function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }


    /**
     * @Route("/interop/app/page/{name?}", name="interop_app_page")
     */
    public function index(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        $user = $this->getUser();
        $name = $request->attributes->get('name');
        $body = "This is Campus Server and you are looking at the contents of page: ".$name;
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, 0, EntityTypeEnum::ENTITY_NULL, 0, 'Updated app info:'.$name);
        return new Response($body);
    }
    
//    /**
//     * @Route("/crl/{name?}", name="interop_crl")
//     */
//    public function crl(Request $request): Response {
//        $user = $this->getUser();
//        $name = $request->attributes->get('name');
//        $body = "This is Campus Server and you are looking at the contents of page: ".$name;
//        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, 0, EntityTypeEnum::ENTITY_NULL, 0, 'Updated app info:'.$name);
//        return new Response($body);
//    }
}
