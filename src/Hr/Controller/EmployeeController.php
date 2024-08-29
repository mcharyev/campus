<?php

namespace App\Hr\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Hr\Form\EmployeeFormType;
use App\Hr\Entity\Employee;
use App\Entity\AlumnusStudent;
use App\Service\PersonalInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use App\Hr\Service\EmployeeManager;
use App\Entity\Nationality;

class EmployeeController extends AbstractController {

    private $systemEventManager;
    private $manager;

    function __construct(EmployeeManager $manager, SystemEventManager $systemEventManager) {
        $this->systemEventManager = $systemEventManager;
        $this->manager = $manager;
    }

    /**
     * @Route("/hr/employee", name="hr_employee")
     * @Route("/hr/employee/search/{searchField?}/{searchValue?}", name="hr_employee_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('hr/controller/employee/index.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/hr/employee/list/{offset?}/{pageSize?}/{sorting?}", name="hr_employee_list")
     * @Route("/hr/employee/list/{offset?0}/{pageSize?20}/{sorting?employee.id}/{searchField?}/{searchValue?}", name="hr_employee_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'employee',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Employee::class);
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
     * @Route("/hr/employee/update", name="hr_employee_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        try {

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Employee::class);
            $employee = $repository->find($id);

            $nationality = $this->getDoctrine()->getRepository(Nationality::class)->find($request->request->get('nationality_id'));

            $updatedEmployee = $this->manager->updateFromRequest($request, $employee, $nationality);

            $repository->update($updatedEmployee);

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
     * @Route("/hr/employee/delete", name="hr_employee_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $employee = $entityManager->getRepository(Employee::class)->find($id);
            $entityManager->remove($employee);
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
     * @Route("/hr/employee/new", name="hr_employee_new")
     * @Route("/hr/employee/edit/{id?0}", name="hr_employee_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_HR");
        $repository = $this->getDoctrine()->getRepository(Employee::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $employee = $repository->find($id);
        } else {
            $employee = new Employee();
        }
        $form = $this->createForm(EmployeeFormType::class, $employee, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $employee = $form->getData();
            $fields = [
                'name', 'position', 'positions', 'relatives', 'education',
                'dob', 'nationality', 'school', 'profession', 'degree', 'languages', 'awards', 'trips', 'mp',
                'address', 'address2', 'phone'
            ];
            $newData = [];
            foreach ($fields as $field) {
                //$newData[] = array($field => $form->get($field)->getData());
                $employee->setDataField($field, $form->get("info_" . $field)->getData());
            }

            $repository->save($employee);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('hr_employee');
        }
        return $this->render('hr/controller/employee/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/hr/employee/info/{id?}", name="hr_employee_info")
     */
    public function employeeInfo(Request $request, PersonalInfoManager $personalInfoManager) {
        $this->denyAccessUnlessGranted(['ROLE_HR']);
        $repository = $this->getDoctrine()->getRepository(Employee::class);
        //$id = 1;
        $access = true;
        $content = '';
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $employee = $repository->find($id);
            if ($employee) {
                $content .= "<a href='/hr/employee/edit/" . $employee->getId() . "'>Edit</a>";
                $content .= " | <a href='/interop/exporter/personalinfo/employee/" . $employee->getSystemId() . "'>Download</a>";
                $access = true;
            }
            if ($access) {
                $info = $employee->getData();
                $content .= $personalInfoManager->viewEmployeeInfo($employee, $info);
            } else {
                $content = 'You are not authorized to view this page.';
            }
        }


        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_EMPLOYEE, $employee->getSystemId(),
                'Employee personal info');
        return $this->render('hr/controller/employee/info.html.twig', [
                    'controller_name' => 'EmployeeController',
                    'pageContent' => $content,
                    'pageTitle' => 'Personal Information'
        ]);
    }

    /**
     * @Route("/hr/employee/listphoto", name="hr_employee_listphoto")
     */
    public function listPhoto(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Employee::class);
        $employee = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        $employeeFolder = $_SERVER['APP_ROOT'] . 'public/build/employee_photos/';
        if ($employee) {
            $file = $employee->getSystemId() . ".jpg";
            $url = '/build/employee_photos/' . $file;
            if (file_exists($employeeFolder . $file)) {
                $body .= "<a href='" . $url . "'>" . $file . "</a><br><br>";
            } else {
                $body .= "Faýl tapylmady: " . $file . "<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/hr/employee/deletephoto", name="hr_employee_deletephoto")
     */
    public function deletePhoto(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Employee::class);
        $employee = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        $employeeFolder = $_SERVER['APP_ROOT'] . 'public/build/employee_photos/';
        if ($employee) {
            $file = $employee->getSystemId() . ".jpg";
            if (file_exists($employeeFolder . $file)) {
                if (unlink($employeeFolder . $file)) {
                    $body .= "Faýl pozuldy: " . $file . "<br>";
                } else {
                    $body .= "Faýl pozulmady: " . $file . "<br>";
                }
            } else {
                $body .= "Faýl tapylmady: " . $file . "<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/hr/employee/uploadphoto", name="hr_empoyee_uploadphoto")
     */
    function uploadPhoto(Request $request) {
        $body = '';
        $allowed = array('jpg', 'JPG', 'jpeg', 'JPEG');
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $body .= 'Faýlyň bu görnüşine rugsat berilmeýär.';
            return new Response($body);
        }

        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Employee::class);
        $employee = $repository->find($id);
        if ($employee) {
            $filename = $employee->getSystemId() . ".jpg";
            $employeeFolder = $_SERVER['APP_ROOT'] . 'public/build/employee_photos/';
            $uploadfile = $employeeFolder . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                $dst = $this->resizeImage($uploadfile, 200, 200);
                $body .= "Surat ýüklendi: " . $filename . "\n";
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_EMPLOYEE, 0, "Upload employee photo: " . $employee->getSystemId());
            } else {
                $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
            }
        }
        return new Response($body);
    }

    private function resizeImage($file, $w, $h, $crop = FALSE) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        //echo $file;
        imagejpeg($dst, $file);
        return $dst;
    }

}
