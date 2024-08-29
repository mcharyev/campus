<?php

namespace App\Controller\LMS;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Entity\TaughtCourse;
use App\Entity\ProgramCourse;
use App\Entity\Department;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Service\SystemEventManager;

/**
 * Controller used to manage IUHDPlus page.
 *
 */
class IuhdplusFileController extends AbstractController {

    use TargetPathTrait;

    private $webroot;

    public function __construct(SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
        $this->webroot = "C:\\campus\\www\\iuhdplus\\";
    }

    /**
     * @Route("/iuhdplus/file/createdirectory", name="iuhdplus_file_createdirectory")
     */
    public function createDirectory(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        $courseId = $request->request->get('course_id');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $course = $taughtCourseRepository->find($courseId);
        $body = '';
        if ($course) {
            $code = $course->getCourseCode();
            $body .= $code . "<br>";
            $course->getDepartment()->getNameEnglish() . "<br>";
            $departmentLetterCode = $course->getDepartmentLetterCode();
            //return;
            if (file_exists($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/")) {
                $body .= $this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . "<br>";
                $body .= "Directory " . $code . " already exists!";
            } else {
                mkdir($this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/", 0777, true);
                $body .= "Directory " . $code . " created!";
            }
        } else {
            $body .= 'Course not found!';
        }

        return new Response($body);
    }

    /**
     * @Route("/iuhdplus/file/uploadcoursedata", name="iuhdplus_file_uploadcoursedata")
     */
    public function uploadCoursedata(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        global $action;
        $allowed = array('xlsx', 'pdf', 'txt', 'jpg', 'docx', 'doc', 'mp4');

        $courseId = $request->request->get('course_id');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $course = $taughtCourseRepository->find($courseId);
        $body = '';
        $action = "uploadcoursedata";

        if ($course) {
            $code = $course->getCourseCode();
            $departmentLetterCode = $course->getDepartmentLetterCode();
            $fileId = 'file' . $course->getId();
            //echo sizeof($_FILES[$file_id]);
            //echo $_FILES[$file_id]['name'];
            $filename = $_FILES[$fileId]['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            //echo $ext;
            if (!in_array($ext, $allowed)) {
                $body .= 'This file type is not allowed.';
                return new Response($body);
            }
            if ($action == "uploaditemfile") {
                $basename = basename($_FILES[$fileId]['name']);
                $uploadfile = $this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $basename;
            } else
                $uploadfile = $this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.' . $ext;

            if (move_uploaded_file($_FILES[$fileId]['tmp_name'], $uploadfile)) {
                //$f1 = getlastfile();
                //$f2 = conv($f2);
                //rename(UPLOADDIR.$f1,UPLOADDIR.$f2);
                $body .= "Upload okay.\n";
                //echo $uploadfile;
                //logtext($username.": ".$filename." fayl yukledi");
                //echo $sql;
                //$conn->query($sql);
            } else {
                $body .= "Possible file upload attack!\n";
                //\Campus\Log::logtext($username.": ".$filename." Possible file upload attack!");
            }
        } else {
            $body .= 'Course not found!';
        }

        return new Response($body);
    }

    /**
     * @Route("/iuhdplus/file/deletecoursedata", name="iuhdplus_file_deletecoursedata")
     */
    public function deleteCoursedata(Request $request, Security $security, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response {
        $courseId = $request->request->get('course_id');
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        $course = $taughtCourseRepository->find($courseId);
        $body = '';
        if ($course) {
            $code = $course->getCourseCode();
            $departmentLetterCode = $course->getDepartmentLetterCode();
            $uploadfile = $this->webroot . '/courses/' . $departmentLetterCode . "/" . $code . "/" . $code . '.xlsx';
            unlink($uploadfile);
            $body .= "File deleted: " . $code . ".xlsx<br>";
        } else {
            $body .= 'Course not found!';
        }

        return new Response($body);
    }

}
