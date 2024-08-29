<?php

namespace App\Controller\System;

use App\Entity\User;
use App\Enum\UserTypeEnum;
use App\Form\UserFormType;
use App\Form\UserResetPasswordFormType;
use App\Entity\EnrolledStudent;
use App\Entity\Teacher;
use App\Entity\Faculty;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class UserController extends AbstractController {

    private $systemEventManager;

    public function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
    }

    /**
     * @Route("/faculty/user", name="faculty_user")
     * @Route("/faculty/user/search/{searchField?}/{searchValue?}", name="faculty_user_search")
     */
    public function index(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('user/index.html.twig', [
                    'controller_name' => 'UserController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/faculty/user/list/{offset?}/{pageSize?}/{sorting?}", name="faculty_user_list")
     * @Route("/faculty/user/list/{offset?0}/{pageSize?20}/{sorting?user.id}/{searchField?}/{searchValue?}", name="faculty_user_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $params = [
                'table' => 'user',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(User::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);            //Return result
            $result_array = [
                'Result' => "OK",
                'TotalRecordCount' => $recordCount,
                'Records' => $results
            ];
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/user/delete", name="faculty_user_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $user = $entityManager->getRepository(User::class)->find($id);
            $entityManager->remove($user);
            $entityManager->flush();
            //Return result
            $result_array = [
                'Result' => "OK"
            ];
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }
        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/account", name="account")
     * @Route("/faculty/user/new", name="faculty_user_new")
     * @Route("/faculty/user/edit/{id?0}", name="faculty_user_edit")
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        //$this->denyAccessUnlessGranted("ROLE_USER");
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $id = $request->attributes->get('id');
        //$user = new User();
        if (!empty($id)) {
            if ($this->getUser()->getId() == $id) {
                $user = $repository->find($id);
            } else {
                if ($this->isGranted("ROLE_ADMIN")) {
                    $user = $repository->find($id);
                } else {
                    return $this->render('Access denied');
                }
            }
        } else {
            if ($this->isGranted("ROLE_ADMIN")) {
                $user = new User();
            } else {
                $user = $repository->find($this->getUser()->getId());
            }
        }


        $form = $this->createForm(UserFormType::class, $user, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'is_admin' => $this->isGranted("ROLE_ADMIN")
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $user = $form->getData();
            if ($this->isGranted("ROLE_ADMIN")) {
                $roles = explode(",", $form->get('roles')->getData());
                $user->setRoles($roles);
            }

            $fields = [
                'security_question1', 'security_answer1', 'security_question2', 'security_answer2'
            ];

            foreach ($fields as $field) {
                $user->setDataField($field, $form->get($field)->getData());
            }

            $newPassword1 = $form->get('newPassword1')->getData();
            $newPassword2 = $form->get('newPassword2')->getData();
            if (strlen($newPassword1) > 0 && $newPassword1 == $newPassword2) {
                $user->setPassword($passwordEncoder->encodePassword($user, $newPassword1));
            }

            $repository->save($user);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_user');
        }
        return $this->render('user/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/user/edituser/{systemId?0}", name="faculty_user_edituser")
     */
    public function editUser(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        //$this->denyAccessUnlessGranted("ROLE_USER");
        $this->denyAccessUnlessGranted('ROLE_USEREDITOR');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $systemId = $request->attributes->get('systemId');
        //$user = new User();
        if (!empty($systemId)) {
            $user = $repository->findOneBy(['systemId' => $systemId]);
            if ($user) {
                $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                $editingTeacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                if ($user->getType() == UserTypeEnum::USER_STUDENT) {
                    $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
                    $student = $studentRepository->findOneBy(['systemId' => $user->getSystemId()]);
                    if (!$student->isDean($editingTeacher)) {
                        //$this->denyAccessUnlessGranted(null);
                        return $this->render('accessdenied.html.twig');
                    }
                } elseif ($user->getType() == UserTypeEnum::USER_TEACHER) {
                    $teacher = $teacherRepository->findOneBy(['systemId' => $user->getSystemId()]);
                    if (!$teacher->isDean($editingTeacher)) {
                        //$this->denyAccessUnlessGranted(null);
                        return $this->render('accessdenied.html.twig');
                    }
                }
            }
        } else {
            return $this->render('accessdenied.html.twig');
        }


        $form = $this->createForm(UserResetPasswordFormType::class, $user, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER')),
            'is_admin' => false
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $user = $form->getData();
            if ($this->isGranted("ROLE_ADMIN")) {
//                $roles = explode(",", $form->get('roles')->getData());
//                $user->setRoles($roles);
            }

            $newPassword1 = $form->get('newPassword1')->getData();
            $newPassword2 = $form->get('newPassword2')->getData();
            if (strlen($newPassword1) > 0 && $newPassword1 == $newPassword2) {
                $user->setPassword($passwordEncoder->encodePassword($user, $newPassword1));
//                $this->ChangeUserPassword($user->getUsername(), $newPassword1);
            }

            $repository->save($user);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_USER, $user->getId(), 'Changed user: ' . $user->getSystemId());
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_user');
        }
        return $this->render('user/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    private function EncodePassword($password) {
        $newPassword = '';
        $password = "\"" . $password . "\"";
        $len = strlen($password);
        for ($i = 0; $i < $len; $i++) {
            $newPassword .= "{$password{$i}}\000";
        }
        $newPassword = base64_encode($newPassword);
        return $newPassword;
    }

    

}
