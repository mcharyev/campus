<?php

namespace App\Controller\Grading;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TranscriptCourse;
use App\Entity\TaughtCourse;
use App\Entity\ProgramCourse;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\StudyProgram;
use App\Entity\Group;
use App\Entity\EnrolledStudent;
use App\Entity\AlumnusStudent;
use App\Entity\Teacher;
use App\Form\TranscriptCourseFormType;
use App\Entity\Setting;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class TranscriptCourseController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/grading/transcriptcourse", name="grading_transcriptcourse")
     * @Route("/grading/transcriptcourse/search/{searchField?}/{searchValue?}", name="grading_transcriptcourse_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('transcript_course/index.html.twig', [
                    'controller_name' => 'TranscriptCourseController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/list", name="grading_transcriptcourse_list")
     * @Route("/grading/transcriptcourse/list/{offset?0}/{pageSize?20}/{sorting?transcript_course.id}/{searchField?}/{searchValue?}", name="grading_transcriptcourse_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $params = [
                'table' => 'transcript_course',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
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
     * @Route("/grading/transcriptcourse/create", name="grading_transcriptcourse_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $transcriptCourse = $this->manager->createFromRequest($request);
            $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
            $repository->save($transcriptCourse);
//*/
            $result = $repository->getLastInserted(['table' => 'transcript_course']);

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
     * @Route("/grading/transcriptcourse/update", name="grading_transcriptcourse_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
            $transcriptCourse = $repository->find($id);

            $updatedTranscriptCourse = $this->manager->updateFromRequest($request, $transcriptCourse);

            $repository->update($updatedTranscriptCourse);

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
     * @Route("/grading/transcriptcourse/delete", name="grading_transcriptcourse_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        try {
            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
            $transcriptCourse = $repository->find($id);
            $repository->remove($transcriptCourse);

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
     * @Route("/grading/transcriptcourse/new", name="grading_transcriptcourse_new")
     * @Route("/grading/transcriptcourse/edit/{id?0}", name="grading_transcriptcourse_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $repository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $transcriptCourse = $repository->find($id);
        } else {
            $transcriptCourse = new TranscriptCourse();
        }
        $form = $this->createForm(TranscriptCourseFormType::class, $transcriptCourse);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
// $form->getData() holds the submitted values
// but, the original `$task` variable has also been updated
            $transcriptCourse = $form->getData();
            $transcriptCourse->setLastUpdater($this->getUser()->getUsername());
            $transcriptCourse->setDateUpdated(new \DateTime());
            $repository->save($transcriptCourse);

            return $this->redirectToRoute('grading_transcriptcourse');
        }
        return $this->render('transcript_course/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/course/view/{id?0}/{courseCode?0}/{groupIds}/{year?}", name="grading_transcriptcourse_course_view")
     */
    public function courseView(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);

        $courseCode = $request->attributes->get('courseCode');
        $courseCodes = explode(",", $courseCode);
        $courseId = $request->attributes->get('id');
        $year = $request->attributes->get('year');
        $groupIds = $request->attributes->get('groupIds');
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];

        if (!empty($courseCode)) {
            $taughtCourse = $taughtCourseRepository->findOneBy([
                'courseCode' => $courseCode,
                'year' => $year,
                'studentGroups' => $groupIds,
                'id' => $courseId,
            ]);
            if (!$taughtCourse) {
                return new Response('Taught course was not found for data: code:' . $courseCode . " year:" . $year);
            } else {
                if ($taughtCourse->getGradingType() == 0) {
                    return $this->render('message.html.twig', [
                                'content' => 'The course is not gradable! Grading type=' . $taughtCourse->getGradingType()
                    ]);
                }

                $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                if ($teacher) {
                    $faculty = $taughtCourse->getDepartment()->getFaculty();
                    $authorizedDeans[] = $faculty->getDean();
                    $authorizedDeans[] = $faculty->getFirstDeputyDean();
//$authorizedDepartmentHeads[] = $group->getDepartmentHead();
                    if (!in_array($teacher, $authorizedDeans) && !in_array($teacher, $authorizedDepartmentHeads)) {
                        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TAUGHTCOURSE, $taughtCourse->getId(), 'Grades viewed');
                        if ($teacher != $taughtCourse->getTeacher()) {
//return new Response($teacher->getSystemId() . '<>' . $taughtCourse->getTeacher()->getSystemId());
                            if (!$this->isGranted("ROLE_ADMIN")) {
                                return $this->render('accessdenied.html.twig');
                            }
                        }
                    }
                } else {
                    if (!$this->isGranted("ROLE_ADMIN")) {
                        return $this->render('accessdenied.html.twig');
                    }
                }

                $midtermStatus = $settingRepository->findOneBy(['name' => 'midterm_status']);
                $finalStatus = $settingRepository->findOneBy(['name' => 'final_status']);
                $makeupStatus = $settingRepository->findOneBy(['name' => 'makeup_status']);
                $siwsiStatus = $settingRepository->findOneBy(['name' => 'siwsi_status']);

                $groupRepository = $this->getDoctrine()->getRepository(Group::class);
                $groupCourses = [];
                $groupCodes = explode(",", $taughtCourse->getStudentGroups());
                $studentGrades = [];
                foreach ($groupCodes as $groupCode) {
                    $groupFound = $groupRepository->findOneBy(['systemId' => $groupCode]);
                    if ($groupFound) {
                        $programCourses = $groupFound->getStudyProgram()->getProgramCourses();
                        $programCourse = null;
                        foreach ($programCourses as $course) {
                            if (strlen($course->getLetterCode()) > 0) {
//echo $course->getLetterCode() . " -- " . $courseCode . " check:" . in_array($course->getLetterCode(), $courseCodes) . "<br>";
                                if (in_array($course->getLetterCode(), $courseCodes)) {
                                    $programCourse = $course;
                                    break;
                                }
                            }
                        }

                        $groupCourses[] = [
                            'group' => $groupFound,
                            'programCourse' => $programCourse,
                        ];
                        $students = $groupFound->getStudents();
                        foreach ($students as $student) {
                            $grade = $transcriptCourseRepository->findOneBy([
                                'letterCode' => $programCourse->getLetterCode(),
                                'year' => $year,
                                'studentId' => $student->getSystemId()
                            ]);
                            if (!$grade) {
                                $grade = new TranscriptCourse();
                                $type = 'new';
                            } else {
                                $type = 'existing';
                            }
                            $studentGrades[] = [
                                'studentId' => $student->getSystemId(),
                                'grade' => $grade,
                                'type' => $type,
                            ];
                        }
                    }
                }
            }
        } else {
            return $this->render('message.html.twig', [
                        'content' => 'Letter code not valid!'
            ]);
        }

        return $this->render('transcript_course/view.html.twig', [
                    'taughtCourse' => $taughtCourse,
                    'studentGrades' => $studentGrades,
                    'programCourse' => $programCourse,
                    'groupCourses' => $groupCourses,
                    'midtermStatus' => $midtermStatus,
                    'finalStatus' => $finalStatus,
                    'makeupStatus' => $makeupStatus,
                    'siwsiStatus' => $siwsiStatus,
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/savegrades", name="grading_transcriptcourse_course_save")
     */
    public function saveGrades(Request $request) {
        $content = '';
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);

        $courseId = $request->request->get('courseId');
        $nameEnglish = $request->request->get('nameEnglish');
        $nameTurkmen = $request->request->get('nameTurkmen');
        $courseCode = $request->request->get('letterCode');
        $creditType = $request->request->get('creditType');
        $courseType = $request->request->get('courseType');
        $year = $request->request->get('year');
        $groupIds = $request->request->get('groupIds');
        $semester = $request->request->get('semester');
        $teacherName = $request->request->get('teacher');
        $lastUpdater = $this->getUser()->getUsername();
        $dateUpdated = new \DateTime();
        if (!empty($courseCode)) {
            $taughtCourse = $taughtCourseRepository->findOneBy([
                'courseCode' => $courseCode,
                'year' => $year,
                'studentGroups' => $groupIds,
                'id' => $courseId,
            ]);
            if (!$taughtCourse) {
                $content = 'Taught course was not found for data: code:' . $courseCode . " year:" . $year;
            } else {
                if ($taughtCourse->getGradingType() == 1) {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if ($teacher != $taughtCourse->getTeacher()) {
                        $this->denyAccessUnlessGranted("ROLE_ADMIN");
                    }
                    $programCourse = $programCourseRepository->findOneBy([
                        'letterCode' => $courseCode
                    ]);
                    $content .= "Course name: " . $nameEnglish . "<br>";
                    $courseCodes = $request->request->get('courseCode');
                    $groupCodes = $request->request->get('groupCode');
                    $credits = $request->request->get('credit');
                    $studentIds = $request->request->get('studentId');
                    $studentNames = $request->request->get('studentName');
                    $midterms = $request->request->get('midterm');
                    $finals = $request->request->get('final');
                    $makeups = $request->request->get('makeup');
                    $siwsis = $request->request->get('siwsi');
                    $courseGrades = $request->request->get('courseGrade');

                    $midtermStatus = $settingRepository->findOneBy(['name' => 'midterm_status']);
                    $finalStatus = $settingRepository->findOneBy(['name' => 'final_status']);
                    $makeupStatus = $settingRepository->findOneBy(['name' => 'makeup_status']);
                    $siwsiStatus = $settingRepository->findOneBy(['name' => 'siwsi_status']);

                    $i = 0;
                    foreach ($studentIds as $studentId) {
//$content .= ($i + 1) . "<br>";
//$content .= 'Student id:' . $studentId . "<br>";
                        $grade = $transcriptCourseRepository->findOneBy([
                            'letterCode' => $courseCodes[$i],
                            'year' => $year,
                            'studentId' => $studentId
                        ]);
                        if (!$grade) {
                            $grade = new TranscriptCourse();
                        }
                        $grade->setStudentId($studentId);
                        $grade->setStudentName($studentNames[$i]);
                        $grade->setNameEnglish($nameEnglish);
                        $grade->setNameTurkmen($nameTurkmen);
                        $grade->setLetterCode($courseCodes[$i]);
                        $grade->setCredits($credits[$i]);
                        $grade->setCreditType($creditType);
                        $grade->setCourseType($courseType);
                        $grade->setYear($year);
                        $grade->setSemester($semester);
                        $grade->setTeacher($teacherName);
                        $grade->setDateUpdated($dateUpdated);
                        $grade->setLastUpdater($lastUpdater);
                        $grade->setCourseId($taughtCourse->getId());
                        $grade->setGroupCode($groupCodes[$i]);
                        $grade->setStatus(1);

                        $midtermGrade = intval($midterms[$i]);
                        if ($midtermGrade >= 0 && $midtermGrade <= 100) {
                            if ($midtermStatus->getValue() == 1) {
                                $grade->setMidterm($midtermGrade);
//$content .= $midtermGrade . "<br>";
                            } else {
                                if ($grade->getMidterm() == null)
                                    $grade->setMidterm(0);
                            }
                        } else {
                            $grade->setMidterm(0);
                            $content .= 'Midterm grade invalid for student id:' . $studentId . "<br>";
                        }

                        $finalGrade = intval($finals[$i]);
                        if ($finalGrade >= 0 && $finalGrade <= 100) {
                            if ($finalStatus->getValue() == 1) {
                                $grade->setFinal($finalGrade);
                            } else {
                                if ($grade->getFinal() == null) {
                                    $grade->setFinal(0);
                                }
                            }
//                            $content .= $finalGrade . ":" . $i . ":" . $studentId . "<br>";
                        } else {
                            $grade->setFinal(0);
                            $content .= 'Final grade invalid for student id:' . $studentId . "<br>";
                        }

                        $makeupGrade = intval($makeups[$i]);
                        if ($makeupGrade >= 0 && $makeupGrade <= 100) {
                            if ($makeupStatus->getValue() == 1) {
                                $grade->setMakeup($makeupGrade);
                            } else {
                                if ($grade->getMakeup() == null)
                                    $grade->setMakeup(0);
                            }
                        } else {
                            $grade->setMakeup(0);
                            $content .= 'Makeup grade invalid for student id:' . $studentId . "<br>";
                        }

                        $siwsiGrade = intval($siwsis[$i]);
                        if ($siwsiGrade >= 0 && $siwsiGrade <= 100) {
                            if ($siwsiStatus->getValue() == 1) {
                                $grade->setSiwsi($siwsiGrade);
                            } else {
                                if ($grade->getSiwsi() == null)
                                    $grade->setSiwsi(0);
                            }
                        } else {
                            $content .= 'SIWSI grade invalid for student id:' . $studentId . "<br>";
                        }

                        $courseGrade = intval($courseGrades[$i]);
                        if ($courseGrade >= 0 && $courseGrade <= 100) {
                            $grade->setCourseGrade($courseGrade);
                        } else {
                            $grade->setCourseGrade($courseGrade);
                            $content .= 'Course grade invalid for student id:' . $studentId . "<br>";
                        }


                        $transcriptCourseRepository->save($grade);
                        $i++;
                    }
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_TAUGHTCOURSE, $taughtCourse->getId(), 'Grades saved');
                    $content .= 'Grades saved successfully!';
                } else {
                    $content .= "This course is not gradable!";
                }
            }
        } else {
            $content .= 'Letter code not valid!';
        }

        return new Response($content);
    }

    /**
     * @Route("/grading/transcriptcourse/student/grades/{systemId}", name="grading_transcriptcourse_student_grades")
     */
    public function studentGrades(Request $request) {
//$this->denyAccessUnlessGranted("ROLE_STUDENT");
        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);

        if ($settingRepository->findOneBy(['name' => 'student_grade_view'])->getValue() == 0) {
            return $this->render('message.html.twig', [
                        'content' => 'Student grades viewing function is disabled at the moment!'
            ]);
        }

        $studentId = $request->attributes->get('systemId');
        $year = $settingRepository->findOneBy(['name' => 'current_year'])->getValue();
        $semester = $settingRepository->findOneBy(['name' => 'current_semester'])->getValue();
        $academic_year = $settingRepository->findOneBy(['name' => 'first_semester_year'])->getValue() . " - " . $settingRepository->findOneBy(['name' => 'second_semester_year'])->getValue();
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];

        if (!empty($studentId)) {
            $student = $enrolledStudentRepository->findOneBy([
                'systemId' => $studentId,
            ]);
            if ($student) {
                if ($student->getSystemId() != $this->getUser()->getSystemId()) {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if ($teacher) {
                        $faculty = $student->getFaculty();
                        $authorizedDeans[] = $faculty->getDean();
                        $authorizedDeans[] = $faculty->getFirstDeputyDean();
//$authorizedDepartmentHeads[] = $group->getDepartmentHead();
                        if (!in_array($teacher, $authorizedDeans) && !in_array($teacher, $authorizedDepartmentHeads)) {
//return new Response('Access denied!');
                            $this->denyAccessUnlessGranted("ROLE_ADMIN");
                        }
                    } else {
//return new Response('Access denied!');
                        $this->denyAccessUnlessGranted("ROLE_ADMIN");
                    }
                }

                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getSystemId(), 'Student grades viewed');
                $transcriptCourses = $transcriptCourseRepository->findBy([
                    'year' => $year,
                    'semester' => $semester,
                    'studentId' => $student->getSystemId()
                ]);
            } else {
                return new Response('Student id not found!');
            }
        } else {
            return new Response('Student id not valid!');
        }

        return $this->render('transcript_course/grades.html.twig', [
                    'student' => $student,
                    'transcriptCourses' => $transcriptCourses,
                    'academic_year' => $academic_year,
                    'year' => $year,
                    'semester' => $semester,
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/student/transcript/{systemId}", name="grading_transcriptcourse_student_transcript")
     */
    public function studentTranscript(Request $request) {
//$this->denyAccessUnlessGranted("ROLE_STUDENT");
        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $programCourseRepository = $this->getDoctrine()->getRepository(ProgramCourse::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);

        if ($settingRepository->findOneBy(['name' => 'student_transcript_view'])->getValue() == 0) {
            return $this->render('message.html.twig', [
                        'content' => 'Student transcript viewing function is disabled at the moment!'
            ]);
        }

        $studentId = $request->attributes->get('systemId');
        $year = $settingRepository->findOneBy(['name' => 'current_year'])->getValue();
        $semester = $settingRepository->findOneBy(['name' => 'current_semester'])->getValue();
        $academic_year = $settingRepository->findOneBy(['name' => 'first_semester_year'])->getValue() . " - " . $settingRepository->findOneBy(['name' => 'second_semester_year'])->getValue();
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];

        if (!empty($studentId)) {
            $student = $enrolledStudentRepository->findOneBy([
                'systemId' => $studentId,
            ]);
            if ($student) {
                if ($student->getSystemId() != $this->getUser()->getSystemId()) {
                    $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                    $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
                    if ($teacher) {
                        $faculty = $student->getFaculty();
                        $authorizedDeans[] = $faculty->getDean();
                        $authorizedDeans[] = $faculty->getFirstDeputyDean();
//$authorizedDepartmentHeads[] = $group->getDepartmentHead();
                        if (!in_array($teacher, $authorizedDeans) && !in_array($teacher, $authorizedDepartmentHeads)) {
                            $this->denyAccessUnlessGranted("ROLE_ADMIN");
                        }
                    } else {
                        $this->denyAccessUnlessGranted("ROLE_ADMIN");
                    }
                }

                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, $student->getSystemId(), 'Student grades viewed');
                $transcriptCourses = $transcriptCourseRepository->findBy([
                    'studentId' => $student->getSystemId()
                        ], [
                    'year' => 'ASC',
                    'semester' => 'ASC',
                ]);

                $transcriptYears = [];
                foreach ($transcriptCourses as $course) {
                    if ($course->getSemester() == 1) {
                        if (!in_array($course->getYear(), $transcriptYears)) {
                            $transcriptYears[] = $course->getYear();
                        }
                    }
                }
            } else {
                return new Response('Student id not found!');
            }
        } else {
            return new Response('Student id not valid!');
        }

        return $this->render('transcript_course/transcript.html.twig', [
                    'student' => $student,
                    'transcriptCourses' => $transcriptCourses,
                    'academic_year' => $academic_year,
                    'year' => $year,
                    'semester' => $semester,
                    'transcriptYears' => $transcriptYears,
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/report/{year?}/{semester?}/{facultySystemId?0}", name="grading_transcriptcourse_report")
     */
    public function gradesReport(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_DEAN");
        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $enrolledStudentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
//$settingRepository = $this->getDoctrine()->getRepository(Setting::class);

        $year = $request->attributes->get('year');
        $semester = $request->attributes->get('semester');
        $facultySystemId = $request->attributes->get('facultySystemId');
        $authorizedDeans = [];
        $authorizedDepartmentHeads = [];
        $reportTitle = 'Grades';

        $transcriptCourses = $transcriptCourseRepository->findBy([
            'year' => $year,
            'semester' => $semester,
                ], ['studentId' => 'ASC']);
        $gradeRows = [];

        if ($facultySystemId == 0) {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
            foreach ($transcriptCourses as $transcriptCourse) {
                $student = $enrolledStudentRepository->findOneBy(['systemId' => $transcriptCourse->getStudentId()]);
                $gradeRows[] = [
                    'transcriptCourse' => $transcriptCourse,
                    'student' => $student,
                ];
            }
        } else {
            $facultyRepository = $this->getDoctrine()->getRepository(Faculty::class);
            $faculty = $facultyRepository->findOneBy(['systemId' => $facultySystemId]);
            $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
            $teacher = $teacherRepository->findOneBy(['systemId' => $this->getUser()->getSystemId()]);
            if ($faculty) {
                if ($teacher) {
                    if ($teacher == $faculty->getDean() || $teacher == $faculty->getFirstDeputyDean() || $this->isGranted("ROLE_ADMIN")) {
                        foreach ($transcriptCourses as $transcriptCourse) {
                            $student = $enrolledStudentRepository->findOneBy(['systemId' => $transcriptCourse->getStudentId()]);
                            if ($student) {
                                if ($student->getFaculty()->getSystemId() == $facultySystemId) {
                                    $gradeRows[] = [
                                        'transcriptCourse' => $transcriptCourse,
                                        'student' => $student,
                                    ];
                                }
                            }
                        }
                    } else {
                        return $this->render('message.html.twig', [
                                    'content' => 'Access denied! System Id:' . $this->getUser()->getSystemId()
                        ]);
                    }
                } else {
                    return $this->render('message.html.twig', [
                                'content' => 'Teacher not found! System Id:' . $this->getUser()->getSystemId()
                    ]);
                }
            } else {
                return $this->render('message.html.twig', [
                            'content' => 'Faculty not found! System Id:' . $facultySystemId
                ]);
            }
        }

        return $this->render('transcript_course/grade_report.html.twig', [
                    'gradeRows' => $gradeRows,
                    'report_title' => $reportTitle,
                    'faculty' => null,
                    'department' => null,
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/listreports", name="grading_transcriptcourse_listreports")
     */
    public function listReports(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $reportTitle = 'Grade reports list';
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);
        $year = $settingRepository->findOneBy(['name' => 'current_year'])->getValue();
        $semester = $settingRepository->findOneBy(['name' => 'current_semester'])->getValue();

        return $this->render('transcript_course/reports_list.html.twig', [
                    'report_title' => $reportTitle,
                    'year' => $year,
                    'semester' => $semester,
        ]);
    }

    /**
     * @Route("/grading/transcriptcourse/ungraded/{type}/{year}/{semester}", name="grading_transcriptcourse_ungraded")
     */
    public function ungradedCourses(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $reportTitle = 'Ungraded courses';
        $settingRepository = $this->getDoctrine()->getRepository(Setting::class);
        $year = $settingRepository->findOneBy(['name' => 'current_year'])->getValue();
        $semester = $settingRepository->findOneBy(['name' => 'current_semester'])->getValue();
        $content = '';

        $transcriptCourseRepository = $this->getDoctrine()->getRepository(TranscriptCourse::class);
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        $taughtCourses = $taughtCourseRepository->findBy(['gradingType' => 1], ['departmentCode' => 'ASC']);
        $content .= "<table class=\"table table-bordered table-sm table-striped small\" id=\"mainTable\">";
        $content .= "<thead>";
        $content .= "<tr>";
        $content .= "<th>#</th>";
        $content .= "<th>Course Name</th>";
        $content .= "<th>Course Code</th>";
        $content .= "<th>Teacher</th>";
        $content .= "<th>Groups</th>";
        $content .= "<th>Department</th>";
        $content .= "<th>Faculty</th>";
        $content .= "<th>Count</th>";
        $content .= "<th>Grade Sum</th>";
        $content .= "<th>Last updater</th>";
        $content .= "</tr></thead>";
        $content .= "<tbody>";
        foreach ($taughtCourses as $taughtCourseOriginal) {
            $taughtCourseRows = [];
            $courseCodes = explode(",", $taughtCourseOriginal->getCourseCode());
            $groups = explode(",", $taughtCourseOriginal->getStudentGroups());
            $i = 0;
            foreach ($courseCodes as $courseCode) {
                $taughtCourseRows[] = [
                    'courseCode' => $courseCode,
                    'groupCode' => $groups[$i],
                    'course' => $taughtCourseOriginal,
                ];
                $i++;
            }
            foreach ($taughtCourseRows as $taughtCourseRow) {
                $taughtCourse = $taughtCourseRow['course'];
                $groupCode = $taughtCourseRow['groupCode'];
                $courseCode = $taughtCourseRow['courseCode'];
                $transcriptCourses = $transcriptCourseRepository->findBy([
                    'year' => $year,
                    'semester' => $semester,
                    'teacher' => $taughtCourse->getTeacher()->getFullname(),
                    'letterCode' => $courseCode,
                    'groupCode' => $groupCode,
                ]);
                $count = sizeof($transcriptCourses);
                $gradeSum = 0;
                $lastUpdater = '';
                foreach ($transcriptCourses as $transcriptCourse) {
//                    $student = $studentRepository->findOneBy(['systemId' => $transcriptCourse->getStudentId()]);
//                    if ($student) {
//                        if ($student->getGroupCode() == $groupCode) {
                    $gradeSum += $transcriptCourse->getMidterm();
                    $lastUpdater = $transcriptCourse->getLastUpdater();
//                        }
//                    } else {
//                        $content .= 'Tapylmady:' . $transcriptCourse->getStudentId();
//                    }
                }
                if ($count == 0 || $gradeSum == 0) {
                    $content .= "<tr>";
                    $content .= "<td>#</td>";
                    $content .= "<td><a href='/grading/transcriptcourse/course/view/" . $taughtCourse->getId() . "/" . $taughtCourse->getCourseCode() . "/" . $taughtCourse->getStudentGroups() . "/" . $taughtCourse->getYear() . "' target=_blank>" . $taughtCourse->getNameEnglish() . "</a></td>";
                    $content .= "<td>" . $courseCode . "</td>";
                    $content .= "<td>" . $taughtCourse->getTeacher()->getFullname() . "</td>";
                    $content .= "<td>" . $groupCode . "</td>";
                    $content .= "<td>" . $taughtCourse->getDepartment()->getNameEnglish() . "</td>";
                    $content .= "<td>" . $taughtCourse->getDepartment()->getFaculty()->getNameEnglish() . "</td>";
                    $content .= "<td>" . $count . "</td>";
                    $content .= "<td>" . $gradeSum . "</td>";
                    $content .= "<td>" . $lastUpdater . "</td>";
                    $content .= "</tr>";
                }
            }
        }
        $content .= "</tbody></table>";

        return $this->render('transcript_course/report.html.twig', [
                    'report_title' => $reportTitle,
                    'year' => $year,
                    'semester' => $semester,
                    'content' => $content,
        ]);
    }

}
