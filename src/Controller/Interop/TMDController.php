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
use App\Entity\Group;

/**
 * Controller used to manage IUHD Campus App.
 *
 */
class TMDController extends AbstractController {

    private $systemEventManager;

    function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/tmd/index", name="tmd_index")
     */
    public function index(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        $body = "This is Campus Server and you are looking at the contents of page.";
        return new Response($body);
    }

    /**
     * @Route("/tmd/students/", name="tmd_students")
     * @Route("/tmd/students/search/{searchField?}/{searchValue?}", name="tmd_students_search")
     */
    public function listStudents(Request $request): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        //$this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('tmd/students.html.twig', [
                    'controller_name' => 'TMDController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'groups' => $groups
        ]);
    }

}
