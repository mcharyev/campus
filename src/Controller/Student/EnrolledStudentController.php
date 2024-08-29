<?php

namespace App\Controller\Student;

use App\Entity\EnrolledStudent;
use App\Service\EnrolledStudentManager;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Service\LdapServiceManager;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\Region;
use App\Entity\HostelRoom;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EnrolledStudentFormType;
use App\Service\PersonalInfoManager;
use Picqer\Barcode\BarcodeGeneratorHTML;
use App\Utility\ImageFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\SettingRepository;

class EnrolledStudentController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $systemInfoManager;
    private $currentYear;
    private $settingsRepository;

    function __construct(EnrolledStudentManager $manager, SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, SettingRepository $settingRepository) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->currentYear = $this->systemInfoManager->getCurrentCommencementYear();
        $this->settingsRepository = $settingRepository;
    }

    /**
     * @Route("/faculty/enrolledstudent", name="faculty_enrolledstudent")
     * @Route("/faculty/enrolledstudent/search/{searchField?}/{searchValue?}", name="faculty_enrolledstudent_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupRepository->findAll();
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $graduationMode = $this->settingsRepository->findOneBy(['name' => 'graduation_mode'])->getValue();
        $expulsionMode = $this->settingsRepository->findOneBy(['name' => 'expulsion_mode'])->getValue();

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('enrolled_student/index.html.twig', [
                    'controller_name' => 'EnrolledStudentController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'groups' => $groups,
                    'year' => $this->currentYear,
                    'graduation_mode' => $graduationMode,
                    'expulsion_mode' => $expulsionMode,
        ]);
    }

    /**
     * @Route("/faculty/enrolledstudent/list", name="faculty_enrolledstudent_list")
     * @Route("/faculty/enrolledstudent/list/{offset?0}/{pageSize?20}/{sorting?enrolled_student.id}/{searchField?}/{searchValue?}", name="faculty_enrolledstudent_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'enrolled_student',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $recordCount = $repository->getRecordCount($params);
            $results = $repository->getRecords($params);
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
     * @Route("/faculty/enrolledstudent/datalist", name="faculty_enrolledstudent_datalist")
     */
    public function datalist(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        try {
            $params = [
                'table' => 'enrolled_student',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $recordCount = $repository->getRecordCount($params);
            $rows = $repository->getRecords($params);
            $datarows = [];
            foreach ($rows as $row) {
                $datarows[] = array_values($row);
            }

            $result_array = $datarows;
        } catch (\Exception $e) {
//Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }

        $response = new Response("{\"data\":" . json_encode($result_array) . "}");
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/enrolledstudent/create", name="faculty_enrolledstudent_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $enrolledStudent = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $repository->save($enrolledStudent);
//*/
            $result = $repository->getLastInserted(['table' => 'enrolled_student']);

//Return result
            $result_array = [
                'Result' => "OK",
                'Record' => $result
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
     * @Route("/faculty/enrolledstudent/update", name="faculty_enrolledstudent_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USEREDITOR");
        try {

            $id = $request->request->get('id');
            $groupId = $request->request->get('student_group_id');
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
            $studentGroup = $groupRepository->find($groupId);
            $enrolledStudent = $repository->find($id);

            $editMode = 1;
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($teacher) {
                if (!$enrolledStudent->isTeacherAuthorized($teacher) && !$this->isGranted("ROLE_ADMIN")) {
                    $editMode = 0;
                }
            }

            if ($editMode == 1) {
                $updatedEnrolledStudent = $enrolledStudent->setSystemId($request->request->get('system_id'))
                        ->setFirstnameEnglish($request->request->get('firstname_english'))
                        ->setLastnameEnglish($request->request->get('lastname_english'))
                        ->setPatronymEnglish($request->request->get('patronym_english'))
                        ->setPreviousLastnameEnglish($request->request->get('previous_lastname_english'))
                        ->setFirstnameTurkmen($request->request->get('firstname_turkmen'))
                        ->setLastnameTurkmen($request->request->get('lastname_turkmen'))
                        ->setPatronymTurkmen($request->request->get('patronym_turkmen'))
                        ->setPreviousLastnameTurkmen($request->request->get('previous_lastname_turkmen'))
                        ->setSubgroup($request->request->get('subgroup'))
                        ->setRegion($this->getDoctrine()->getRepository(Region::class)->find($request->request->get('region')))
                        ->setHostelRoom($this->getDoctrine()->getRepository(HostelRoom::class)->find($request->request->get('hostel_room_id')))
                        ->setGender($request->request->get('gender'))
                        ->setStudentGroup($studentGroup)
                        ->setGroupCode($studentGroup->getSystemId());
                $repository->update($updatedEnrolledStudent);

                if ($request->request->get('data')) {
                    $repository->updateData($updatedEnrolledStudent, $request->request->get('data'));
                }

                $result_array = [
                    'Result' => "OK"
                ];
            } else {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => 'You are not authorized to edit this item',
                ];
            }
        } catch (\Exception $e) {
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
     * @Route("/faculty/enrolledstudent/delete", name="faculty_enrolledstudent_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $item = $repository->find($id);
            $repository->remove($item);

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
     * @Route("/faculty/enrolledstudent/new", name="faculty_enrolledstudent_new")
     * @Route("/faculty/enrolledstudent/edit/{id?0}/{source_path?}", name="faculty_enrolledstudent_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $id = $request->attributes->get('id');
//$sourcePath = $request->attributes->get('source_path');
        if (!empty($id)) {
            $enrolledStudent = $repository->find($id);
        } else {
            $enrolledStudent = new EnrolledStudent();
        }
        $form = $this->createForm(EnrolledStudentFormType::class, $enrolledStudent, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
// $form->getData() holds the submitted values
// but, the original `$task` variable has also been updated
            $enrolledStudent = $form->getData();
//            $position = $form->get('position')->getData();
//            $positions = $form->get('positions')->getData();
//            $relatives = $form->get('relatives')->getData();
            $nationality = $enrolledStudent->getNationality()->getNameTurkmen();
            $fields = [
                'position', 'positions', 'relatives', 'education',
                'dob', 'school', 'profession', 'degree', 'languages', 'awards', 'trips', 'mp',
                'address', 'address2', 'temporary_registration_address', 'permanent_address', 'phone', 'mobile_phone', 'thesis', 'thesis_advisor', 'internship_place',
                'internship_advisor', 'diploma_registration_number', 'diploma_type',
                'diploma_number', 'diploma_order_date', 'diploma_receive_date', 'diploma_note',
                'diploma_chairman', 'employment_place', 'employment_position', 'employment_note'
            ];
//$newData = [];
            foreach ($fields as $field) {
//$newData[] = array($field => $form->get($field)->getData());
                $enrolledStudent->setDataField($field, $form->get($field)->getData());
            }

            $enrolledStudent->setDataField('nationality', $nationality);
            $enrolledStudent->setDataField('name', $enrolledStudent->getThreenames());

            $repository->save($enrolledStudent);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE,
                    EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(),
                    EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $enrolledStudent->getId(), 'Student info');
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('faculty_enrolledstudent');
        }
        return $this->render('enrolled_student/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/faculty/enrolledstudent/info/{id?}", name="faculty_enrolledstudent_info")
     */
    public function studentInfo(Request $request, PersonalInfoManager $personalInfoManager) {
        $this->denyAccessUnlessGranted(['ROLE_TEACHER', 'ROLE_DEAN', 'ROLE_DEPARTMENTHEAD', 'ROLE_STUDENT']);
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
//$id = 1;
        $access = true;
        $content = '';
        $id = $request->attributes->get('id');
        if (empty($id)) {
            $student = $studentRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($student) {
                $id = $student->getSystemId();
                $access = true;
            }
        }

        if (!empty($id)) {
            $authorizedTeachers = [];

//$id = 1;
            $student = $studentRepository->findOneBy(['systemId' => $id]);
            if ($student) {
                $access = false;
                if ($this->getUser()->getSystemId() == $student->getSystemId()) {
                    $access = true;
                } else {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if ($student->isTeacherAuthorized($teacher) || $this->isGranted("ROLE_SPECIALIST")) {
                        $content .= "<a href='/interop/exporter/personalinfo/enrolled/" . $student->getSystemId() . "'>Download</a>";
                        $content .= " | <a href='/interop/exporter/personalinfo/enrolled/" . $student->getSystemId() . "/mejlis'>Download (Mejlis)</a>";
                        $access = true;
                    }
                    if ($this->isGranted("ROLE_SPECIALIST")) {
                        $content .= " | <a href='/faculty/enrolledstudent/edit/" . $student->getId() . "'>Edit</a>";
                        $content .= " | <a href='/faculty/enrolledstudent/search/group_code/" . $student->getStudentGroup()->getSystemId() . "'>Return to group</a>";
                    }
                    if ($this->isGranted("ROLE_DEAN")) {
                        
                    }
                }
                if ($access) {
                    $info = $student->getData();
                    $content .= $personalInfoManager->viewStudentInfo($student, $info);
                } else {
                    $content = 'You are not authorized to view this page.';
                }
            }
        }

        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getId(),
                'Student personal info');
        return $this->render('enrolled_student/info.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'pageContent' => $content,
                    'pageTitle' => 'Personal Information'
        ]);
    }

    /**
     * @Route("/faculty/enrolledstudent/jsonlist/{term?}", name="faculty_enrolledstudent_jsonlist")
     * @Route("/faculty/enrolledstudent/jsonlistitems/{ids?}", name="faculty_enrolledstudent_jsonlistitems")
     */
    public function jsonlist(Request $request) {
//$this->denyAccessUnlessGranted("ROLE_TEACHER");
        $result_array = array();
        $term = $request->attributes->get('term');
        $ids = $request->attributes->get('ids');
        try {
            $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            if (!empty($ids)) {
                $items = explode(",", $ids);
                foreach ($items as $item) {
                    $student = $enrolledStudentRepository->findOneBy(['systemId' => $item]);
                    if ($student) {
                        $result_array[] = array(
                            "id" => $student->getSystemId(),
                            "study_program_turkmen" => $student->getMajorInTurkmen(),
                            "study_year" => $student->getStudentGroup()->getStudyYear(),
                            "name" => $student->getThreeNames(),
                        );
                    }
                }
            } else {
//if ($letterCode) {
                $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
                $students = $enrolledStudentRepository->findLikeByField("lastname_turkmen", $term);
                foreach ($students as $student) {
                    $result_array[] = array(
                        "id" => $student->getSystemId(),
                        "study_program_turkmen" => $student->getMajorInTurkmen(),
                        "study_year" => $student->getStudentGroup()->getStudyYear(),
                        "name" => $student->getThreeNames(),
                    );
                }
            }
        } catch (\Exception $e) {
//Return error message
            $result_array[] = [
                'id' => "ERROR",
                'value' => $e->getMessage()
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/faculty/enrolledstudent/idcard/{groupCode?}", name="faculty_enrolled_student_idcard")
     */
    public function idcard(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $generator = new BarcodeGeneratorHTML();
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupCode = $request->attributes->get('groupCode');
        $group = $groupRepository->findOneBy(['systemId' => $groupCode]);
        $students = $group->getStudents();
        $content = "";
        $today = new \DateTime();
        $i = 0;
        if ($group) {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($group->isTeacherAuthorized($teacher) || $this->isGranted("ROLE_SPECIALIST") || ($group->getFaculty()->getLetterCode() == "IEM" && $teacher->getSystemId() == 5050)) {
                foreach ($students as $student) {
                    if ($i < 50) {
                        $content .= "<div class='card'>";
                        $content .= "<div class='toprow'>";
                        $content .= "<div class='emblem'><img src='/build/images/emblem.png' class='emblemimage'></div>";
                        $content .= "<div class='university'>Halkara ynsanperwer ylymlary <br>we ösüş uniwersiteti</div>";
                        $content .= "<div class='flag'><img src='/build/images/flag.png' class='flagimage'></div>";
                        $content .= "</div>";
                        $content .= "<div class='midrow'>";
                        $content .= "<div class='leftpanel'>";
                        $content .= "<div class='status'>TALYP</div>";
                        $content .= "<div class='name'>" . $student->getLastnameTurkmen() . "</div>"
                                . "<div class='name'>" . $student->getFirstnameTurkmen() . "</div>"
                                . "<div class='name'>" . $student->getPatronymTurkmen() . "</div>";
                        $content .= "<div class='barcode'>" . $generator->getBarcode("IUHD" . $student->getSystemId(), $generator::TYPE_CODE_128, 1.5, 30) . "</div>";
                        $letterCode = substr($group->getStudyProgram()->getLetterCode(), 0, strlen($group->getStudyProgram()->getLetterCode()) - 4);
                        $content .= "<div class='code'>IUHD " . $group->getFaculty()->getLetterCode() . " " . $group->getStudyYear() . $letterCode . " " . $student->getSystemId() . "</div>";
                        $content .= "</div>";
                        $content .= "<div class='midpanel'>";
                        $year1 = substr(strval($student->getSystemId()), 0, 2);
                        $year2 = substr(strval($group->getGraduationYear()), 2, 2);
                        $content .= "<div class='years'>" . $year1 . "/" . $year2 . "</div>";
                        $content .= "</div>";
                        $content .= "<div class='rightpanel'>";
                        $content .= "<img src='/build/photos/" . $groupCode . "/" . $student->getSystemId() . ".jpg' class='photo'>";
                        $content .= "</div>";
                        $content .= "</div>";
                        $content .= "<div class='bottomrow " . $group->getFaculty()->getLetterCode() . "'>";
                        $content .= "<div class='facultyname'>Fakulteti: " . $group->getFaculty()->getNameTurkmen() . "</div>";
                        $content .= "<div class='majorname'>Hünäri: " . $group->getStudyProgram()->getNameTurkmen() . "</div>";
                        $content .= "</div>";
                        $content .= "</div>";
                        $i++;
                    }
                }
            } else {
                $content = "Access denied!";
            }
        } else {
            $content = "Group not found!";
        }

        return $this->render('enrolled_student/idcard.html.twig', [
                    'controller_name' => 'EnrolledStudentController',
                    'content' => $content,
                    'group' => $group,
                    'year' => $this->currentYear,
        ]);
    }

    /**
     * @Route("/faculty/enrolledstudent/updatefield/{id}/{field}/{value}", name="faculty_enrolledstudent_updatefield")
     */
    public function updatefield(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $id = $request->attributes->get('id');
        $field = $request->attributes->get('field');
        $value = $request->attributes->get('value');
        $enrolledStudent = $repository->find($id);
        switch ($field) {
            case 'subgroup':
                $enrolledStudent->setSubgroup($value);
                break;
        }
        $repository->save($enrolledStudent);

        return new Response("<span style='color:green'>Updated " . $field . "=" . $value . "</span>");
    }

    /**
     * @Route("/faculty/enrolledstudent/usercreate/{id?}", name="faculty_enrolledstudent_usercreate")
     */
    public function createUser(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $id = $request->attributes->get('id');
        $content = "<br>User creation: ";

        $enrolledStudent = $repository->find($id);
        $username = $enrolledStudent->getSystemId();
        $currentYear = $this->systemInfoManager->getCurrentCommencementYear();
        $currentYearString = strval($currentYear);
        $idCheckYear = intval(substr($currentYear, 2, 2) . "0000");
        if (intval($username) < $idCheckYear) {
            $content .= " User already exists because student id is below " . $idCheckYear . "!<br>";
        } else {
            $firstname = $enrolledStudent->getFirstnameTurkmen();
            $lastname = $enrolledStudent->getLastnameTurkmen();
            $patronym = $enrolledStudent->getPatronymTurkmen();
            $password = "123456";
            $department = $enrolledStudent->getDepartment()->getNameTurkmen();

            $ldap = ldap_connect($_SERVER['APP_AD_SERVER']);
            $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $_SERVER['APP_AD_USER'];
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

            $bind = @ldap_bind($ldap, $ldaprdn, $_SERVER['APP_AD_PASSWORD']);
            if ($bind) {
                if (strlen($patronym) > 0) {
                    $fullname = $lastname . " " . $firstname . " " . $patronym;
                } else {
                    $fullname = $lastname . " " . $firstname;
                }



                $dn = "CN=" . $fullname . ",OU=" . $currentYearString . ",OU=Students,OU=UNI,DC=UNI,DC=tm";
//CN=Ata Atayew\ ,OU=2019,OU=Students,OU=UNI,DC=UNI,DC=tm
/// prepare data
                $entry["sAMAccountName"] = $username;
                $entry["cn"] = $fullname;
                $entry["displayName"] = $fullname;
                $entry["sn"] = $lastname;
                $entry["givenName"] = $firstname;
                $entry["l"] = "Ashgabat";
                $entry["userAccountControl"] = 512;
                $entry["objectClass"][0] = "top";
                $entry["objectClass"][1] = "person";
                $entry["objectClass"][2] = "organizationalPerson";
                $entry["objectClass"][3] = "user";
                $entry['department'] = $department;
                $entry['unicodePwd'] = iconv("UTF-8", "UTF-16LE", '"' . $password . '"');
                $entry['pwdLastSet'] = 0;

// add data to directory
                try {
                    $result = ldap_add($ldap, $dn, $entry);
                    if ($result) {
                        $content .= "AD User was created: " . $username . "<br>";
                    } else {
                        $content .= "AD There was a problem: " . ldap_error($ldap) . "<br>";
                    }
                } catch (\Exception $ex) {
                    $content .= $ex->getMessage() . "<br>";
                }

//            $userdata['unicodePwd'] = $this->convertPasswordToActiveDirectory($newPassword);
//$result = false;
            }
            ldap_close($ldap);

            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepository->findOneBy(['systemId' => $username]);
            if ($user) {
                $content .= "Campus User already exists!";
            } else {
                try {
                    $newUser = new User();
                    $newUser->setFirstname($firstname);
                    $newUser->setLastname($lastname);
                    $newUser->setPassword($passwordEncoder->encodePassword($newUser, $password));
                    $newUser->setSystemId($username);
                    $newUser->setType(1); //student type
                    $newUser->setRoles(['ROLE_STUDENT']);
                    $newUser->setUsername($username);
                    $newUser->setEmail($username . "@uni.tm");
                    $userRepository->save($newUser);
                    $content .= "Campus User created!<br>";
                } catch (\Exception $ex) {
                    $content .= $ex->getMessage() . "<br>";
                }
            }
        }

        return new Response($content);
    }

    /**
     * @Route("/faculty/enrolledstudent/userchangepassword/{id?}/{password?}", name="faculty_enrolledstudent_userchangepassword")
     */
    public function userChangePassword(Request $request, LdapServiceManager $ldapServiceManager) {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //$this->denyAccessUnlessGranted("ROLE_USER_EDITOR");
        $content = "User changing password: ";
        if ($this->isGranted("ROLE_USEREDITOR")) {
            $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
            $id = $request->attributes->get('id');
            $password = $request->attributes->get('password');

            $enrolledStudent = $repository->find($id);
            $username = $enrolledStudent->getSystemId();
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($enrolledStudent) {
                if ($enrolledStudent->isTeacherAuthorized($teacher) || $this->isGranted("ROLE_ADMIN")) {
                    //$content .= "ok";
                    $content .= $ldapServiceManager->changeUserPassword($username, $password);
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                            $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, "Change password OK: " . $enrolledStudent->getSystemId());
                } else {
                    $content .= "Access denied for: " . $enrolledStudent->getSystemId();
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                            $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, "Change password FAIL: " . $enrolledStudent->getSystemId());
                }
            } else {
                $content .= "User not found.";
            }
        } else {
            $content .= "Insufficient privileges for password change.";
        }
        return new Response($content);
    }

    /**
     * @Route("/faculty/enrolledstudent/customlist/{groupCode?}", name="faculty_enrolled_student_customlist")
     */
    public function customList(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $generator = new BarcodeGeneratorHTML();
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $groupCode = $request->attributes->get('groupCode');
        $group = $groupRepository->findOneBy(['systemId' => $groupCode]);
        $students = $group->getStudents();
        $content = "";
        $today = new \DateTime();
        $i = 0;
        if ($group) {
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($group->isTeacherAuthorized($teacher) || $this->isGranted("ROLE_SPECIALIST")) {
                $content .= "username,password,firstname,lastname,email<br>";
                foreach ($students as $student) {
                    if ($i < 50) {
                        $content .= $student->getSystemId() . ",pass!" . $student->getSystemId() . "," . $student->getFirstnameTurkmen() . "," . $student->getLastnameTurkmen() . "," . $student->getSystemId() . "@iuhd.edu.tm<br>";
                        $i++;
                    }
                }
            } else {
                $content = "Access denied!";
            }
        } else {
            $content = "Group not found!";
        }

        return $this->render('enrolled_student/idcard.html.twig', [
                    'controller_name' => 'EnrolledStudentController',
                    'content' => $content,
                    'group' => $group,
        ]);
    }

    /**
     * @Route("/faculty/enrolledstudent/uploadphoto", name="faculty_enrolled_student_uploadphoto")
     */
    function uploadPhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $body = '';
        $allowed = array('jpg', 'JPG', 'jpeg', 'JPEG');
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $body .= 'Faýlyň bu görnüşine rugsat berilmeýär.';
            return new Response($body);
        }

        $imageFile = new ImageFile();

        $id = $request->request->get('id', 0);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $enrolledStudent = $enrolledStudentRepository->find($id);
        if ($enrolledStudent) {
            $filename = $enrolledStudent->getSystemId() . ".jpg";
            $uploadfile = $this->systemInfoManager->getPublicBuildPath() . "/photos/" . $enrolledStudent->getGroupCode() . "/" . $filename;
            try {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
//                $body .= "<br>".$uploadfile;
//                $body .= "<br>".$uploadfileoriginal;
                    $dst = $imageFile->resizeImageHeight($uploadfile, 300);
                    $body .= "Surat ýüklendi: " . $filename . "\n";
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                            $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, "Upload photo: " . $enrolledStudent->getSystemId());
                } else {
                    $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
                }
            } catch (\Exception $e) {
                $body = $e->getMessage();
            }
//            $body .= 'okay ';
        }
        return new Response($body);
    }

    /**
     * @Route("/faculty/enrolledstudent/listphoto", name="faculty_enrolled_student_listphoto")
     */
    public function listPhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USEREDITOR");
        $body = '';
        $id = $request->request->get('id', 0);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $enrolledStudent = $enrolledStudentRepository->find($id);
//$body .= "Item call number:" . $item_callnumber;
        if ($enrolledStudent) {
            $filename = $enrolledStudent->getSystemId() . ".jpg";
            $filePath = $this->systemInfoManager->getPublicBuildPath() . "/photos/" . $enrolledStudent->getGroupCode() . "/" . $filename;
            if (file_exists($filePath)) {
                $body .= "<a href='/build/photos/" . $enrolledStudent->getGroupCode() . "/" . $filename . "'>" . $filename . "</a><br><br>";
            } else {
                $body .= "Faýl ýok.<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/faculty/enrolledstudent/deletephoto", name="faculty_enrolled_student_deletephoto")
     */
    public function deletePhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $body = '';
        $id = $request->request->get('id', 0);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $enrolledStudent = $enrolledStudentRepository->find($id);
//$body .= "Item call number:" . $item_callnumber;
        if ($enrolledStudent) {
            $filename = $enrolledStudent->getSystemId() . ".jpg";
            try {
                $filePath = $this->systemInfoManager->getPublicBuildPath() . "/photos/" . $enrolledStudent->getGroupCode() . "/" . $filename;
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        $body .= "Faýl pozuldy: " . $filename . "<br>";
                    } else {
                        $body .= "Faýl pozulmady: " . $filename . "<br>";
                    }
                } else {
                    $body .= "Faýl tapylmady: " . $filename . "<br>";
                }
            } catch (\Exception $e) {
                $body = $e->getMessage();
            }
        } else {
            $body .= "Invalid data: " . $id . "<br>";
        }
        return new Response($body);
    }

}
