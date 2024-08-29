<?php

namespace App\Library\Controller;

use App\Library\Entity\LibraryItem;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryLoan;
use App\Entity\Language;
use App\Library\Form\LibraryItemFormType;
use App\Library\Service\LibraryItemManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Library\Repository\LibraryUnitRepository;
use App\Entity\User;
use App\Entity\EnrolledStudent;
use App\Entity\Teacher;
use App\Hr\Entity\Employee;
use Picqer\Barcode\BarcodeGeneratorHTML;
use App\Repository\SettingRepository;

class LibraryItemController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;
    private $settingsRepository;

    function __construct(LibraryItemManager $manager, SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository, SettingRepository $settingRepository) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->settingsRepository = $settingRepository;

        if ($security->getUser()) {
            $libraryAccesses = $security->getUser()->getLibraryAccesses();
            if (sizeof($libraryAccesses) > 0) {
                $this->libraryUnit = $libraryUnitRepository->find($libraryAccesses[0]->getLibraryUnit()->getId());
            } else {
                $this->libraryUnit = null;
            }
        } else {
            $this->libraryUnit = null;
        }
        if ($this->libraryUnit) {
            $libraryUnitId = $this->libraryUnit->getId();
        } else {
            $libraryUnitId = 1;
        }
        $rootPath = $this->settingsRepository->findOneBy(['name' => 'root_path'])->getValue();
        $this->itemsFolder = $rootPath . "/public/build/library/" . $libraryUnitId . "/items/photos/";
        $this->itemsFolderOriginal = $rootPath . "/public/build/library/" . $libraryUnitId . "/items/photos_original/";
    }

    /**
     * @Route("/library/libraryitem", name="library_libraryitem")
     * @Route("/library/libraryitem/search/{searchField?}/{searchValue?}", name="library_libraryitem_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $searchField . "=" . $searchValue);
        return $this->render('library/libraryitem.html.twig', [
                    'controller_name' => 'LibraryItemController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/libraryitem/list", name="library_libraryitem_list")
     * @Route("/library/libraryitem/list/{offset?0}/{pageSize?20}/{sorting?library_item.id}/{searchField?}/{searchValue?}", name="library_libraryitemsearch_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            //echo "Library unit:".$request->request->get('library_unit_id');
            $params = [
                'table' => 'library_item',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'libraryUnitId' => $this->libraryUnit->getId()
            ];
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
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
     * @Route("/library/libraryitem/create", name="library_libraryitem_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $item = $this->manager->createFromRequest($request, $language, $this->libraryUnit);
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $repository->save($item);
            //*/
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $item->getMainTitle());
            $result = $repository->getLastInserted(['table' => 'library_item', 'libraryUnitId' => $this->libraryUnit->getId()]);

            //Return result
            $result_array = [
                'Result' => "OK",
                'Record' => $result,
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
     * @Route("/library/libraryitem/update", name="library_libraryitem_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $item = $repository->find($id);

            $updatedItem = $this->manager->updateFromRequest($request, $item, $language, $this->libraryUnit);
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $item->getMainTitle());
            if ($this->libraryUnit != $item->getLibraryUnit()) {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => "This item does not belong in your library"
                ];
            } else {
                $repository->update($updatedItem);
                $result_array = [
                    'Result' => "OK",
                ];
            }
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
     * @Route("/library/libraryitem/delete/{id?}", name="library_libraryitem_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $item = $repository->find($id);
            if ($this->libraryUnit != $item->getLibraryUnit()) {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => "This item does not belong in your library"
                ];
            } else {
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_DELETE, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $item->getMainTitle());
                $repository->remove($item);
                //Return result
                $result_array = [
                    'Result' => "OK"
                ];
            }
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
     * @Route("/library/libraryitem/multiplyitem", name="library_libraryitem_multiplyitem")
     */
    public function multiplyItem(Request $request) {
        $body = '';
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $item_count = $request->request->get('item_count', 0);
        $item_callnumber = $request->request->get('item_callnumber', 0);
        if ($item_count > 0 && $item_callnumber > 0) {
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $libraryItem = $repository->findOneBy(['callNumber' => $item_callnumber]);
            //$body .= "Item call number:" . $item_callnumber;
            if ($libraryItem) {
                $item_copynumber = intval($libraryItem->getCopyNumber()) + 1;
                $item_callnumbernew = intval($libraryItem->getCallNumber()) + 1;
                for ($i = 0; $i < $item_count; $i++) {
                    $item_callnumbernew_padded = str_pad($item_callnumbernew, 6, "0", STR_PAD_LEFT);
                    $newItem = clone $libraryItem;
                    $newItem->setCopyNumber($item_copynumber);
                    $newItem->setCallNumberOriginal($item_callnumber);
                    $newItem->setCallNumber($item_callnumbernew_padded);

                    try {
                        $repository->save($newItem);
                    } catch (Exception $ex) {
                        $body .= "Error: " . $e->getMessage();
                    }
                    $item_callnumbernew++;
                    $item_copynumber++;
                }
                $body .= "Item record multiplied with: " . $item_count . " items";
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, "Multiply: " . $libraryItem->getMainTitle());
            } else {
                $body .= "Item record ID " . $item_callnumber . " not found!";
            }
        } else {
            $body .= "Wrong parameters";
        }

        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/fixcallnumber", name="library_libraryitem_fixcallnumber")
     */
    public function fixCallNumber(Request $request) {
        $body = '';
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $libraryItems = $repository->findAll();
        foreach ($libraryItems as $libraryItem) {
            if ($libraryItem->getCallNumberOriginal() == '') {
                $libraryItem->setCallNumberOriginal($libraryItem->getCallNumber());
            } elseif (strlen($libraryItem->getCallNumberOriginal()) < 6) {
                $libraryItem->setCallNumberOriginal(str_pad($libraryItem->getCallNumberOriginal(), 6, "0", STR_PAD_LEFT));
            }
            $repository->save($libraryItem);
            $body .= "Item record fixed: " . $libraryItem->getCallNumber() . ": " . $libraryItem->getCallNumberOriginal();
        }
        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/listitemphoto", name="library_libraryitem_listitemphoto")
     */
    public function listItemPhoto(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $libraryItem = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($libraryItem) {
            if (strlen($libraryItem->getCallNumberOriginal()) == 0)
                $file = $libraryItem->getCallNumber() . ".jpg";
            else
                $file = $libraryItem->getCallNumberOriginal() . ".jpg";
            //echo ITEMS_DIR.$file;
            if (file_exists($this->itemsFolder . $file)) {
                $body .= "<a href='/build/library/" . $this->libraryUnit->getId() . "/items/photos/" . $file . "'>" . $file . "</a><br><br>";
            } else {
                $body .= "Faýl tapylmady: " . $file . "<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/uploaditemphoto", name="library_libraryitem_uploaditemphoto")
     */
    function uploadItemPhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $allowed = array('jpg', 'JPG', 'jpeg', 'JPEG');
        try {
            $filename = $_FILES['file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                $body .= 'Faýlyň bu görnüşine rugsat berilmeýär.';
                return new Response($body);
            }

            $id = $request->request->get('id', 0);
            $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $libraryItem = $repository->find($id);
            if ($libraryItem) {
                $filename = $libraryItem->getCallNumberOriginal() . ".jpg";
                $uploadfile = $this->itemsFolder . $filename;
                $uploadfileoriginal = $this->itemsFolderOriginal . $filename;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
//                $body .= "<br>".$uploadfile;
//                $body .= "<br>".$uploadfileoriginal;
                    copy($uploadfile, $uploadfileoriginal);
                    $dst = $this->resizeImage($uploadfile, 200, 200);
                    $body .= "Surat ýüklendi: " . $filename . "\n";
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                            $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, "Upload photo: " . $libraryItem->getMainTitle());
                } else {
                    $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
                }
//            $body .= 'okay ';
            }
        } catch (\Exception $e) {
            $body .= $e->getCode() . " " . $e->getMessage();
            $body .= " " . $e->getFile() . " " . $e->getLine();
        }
        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/deleteitemphoto", name="library_libraryitem_deleteitemphoto")
     */
    public function deletePhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $libraryItem = $repository->find($id);
        if ($libraryItem) {
            $filename = $libraryItem->getCallNumberOriginal() . ".jpg";
            $smallFilePath = $this->itemsFolder . $filename;
            $bigFilePath = $this->itemsFolderOriginal . $filename;
            try {
                if (file_exists($smallFilePath)) {
                    if (unlink($smallFilePath)) {
                        $body .= "Faýl pozuldy: " . $filename . "<br>";
                    } else {
                        $body .= "Faýl pozulmady: " . $filename . "<br>";
                    }
                } else {
                    $body .= "Faýl tapylmady: " . $filename . "<br>";
                }
                if (file_exists($bigFilePath)) {
                    if (unlink($bigFilePath)) {
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

    /**
     * @Route("/library/libraryitem/loanreturn", name="library_libraryitem_loanreturn")
     */
    function loanReturnMain(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        return $this->render('library/loanreturn.html.twig', [
                    'controller_name' => 'LibraryItemController',
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/libraryitem/loanreturn/searchuser", name="library_libraryitem_loanreturn_searchuser")
     */
    function loanReturnSearchUser(Request $request) {
        $body = '';
//        $loan_id = $request->request->get("loan_id", 0);
//        $loan_userid = $request->request->get("loan_userid", 0);
        //$loan_usernumber = $request->request->get("loan_usernumber", 0);
        $loan_usernumber = $request->request->get("loan_usernumber");
//        $loan_itemcallnumber = $request->request->get("loan_itemcallnumber", 0);
//        $loan_type = $request->request->get("loan_type", 0);
//        $loan_authorid = $request->request->get("loan_authorid", 0);
//        $loan_username = $request->request->get("loan_username", '');
        //$body .= $loan_usernumber;
        //$body .= "Request:".$request->request->get("loan_usernumber");
        $check_id = str_replace("IUHD", "", $loan_usernumber);
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['systemId' => $loan_usernumber]);
        if ($user) {
            $userId = $user->getId();
            $lastname = $user->getLastname();
            $firstname = $user->getFirstname();
            $photo = '';
            $systemId = 'Not found!';
            $additionalInfo = '';
            //STUDENT
            if (strlen($loan_usernumber) == 6) {
                $studentRepository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
                $student = $studentRepository->findOneBy(['systemId' => $loan_usernumber]);
                if ($student) {
                    $systemId = $student->getSystemId();
                    $photo = "/build/photos/" . $student->getStudentGroup()->getSystemId() . "/" . $student->getSystemId() . ".jpg";
                    $additionalInfo = $student->getFaculty()->getNameTurkmen() . "<br>" . $student->getMajorInTurkmen() . "<br>" . $student->getStudentGroup()->getStudyYear() . " -nji(y) ýyl";
                } else {
                    //$systemId = 0;
                    $photo = "/build/photos/blank.jpg";
                }
            } elseif (strlen($loan_usernumber) == 4) {
                //TEACHER
                $teacherRepository = $this->getDoctrine()->getRepository(Teacher::class);
                $teacher = $teacherRepository->findOneBy(['systemId' => $loan_usernumber]);
                if ($teacher) {
                    $systemId = $teacher->getSystemId();
                    $photo = "/build/teachers/" . $teacher->getSystemId() . ".jpg";
                } else {
                    //$systemId = 0;
                    $photo = "/build/photos/blank.jpg";
                }
            } elseif (strlen($loan_usernumber) == 3) {
                //EMPLOYEE
                $employeeRepository = $this->getDoctrine()->getRepository(Employee::class);
                $employee = $employeeRepository->findOneBy(['systemId' => $loan_usernumber]);
                if ($employee) {
                    $systemId = $employee->getSystemId();
                    $photo = "/build/employee_photos/" . $employee->getSystemId() . ".jpg";
                } else {
                    //$systemId = 0;
                    $photo = "/build/photos/blank.jpg";
                }
            }

            $body .= "<table width='95%' align='center' border=0>";
            $body .= "<tr><td align='center'><img src='" . $photo . "' height='210' class='userphoto'></td></tr>";
            $body .= "<tr><td align='center'><span class='text-big'>" . $lastname . " " . $firstname . "</span></td></tr>";
            $body .= "<tr><td align='center'><span class='text-big'>" . $systemId . "</span></td></tr>";
            $body .= "<tr><td align='center'><span class='text-big'>" . $additionalInfo . "</span></td></tr>";
            $body .= "</table>";
            $body .= "<script>setuser(new Array('" . $lastname . " " . $firstname . "','" . $userId . "'));</script>";
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_USER, $user->getId(), "Library: User found");
        } else {
            $body .= "User not found!";
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_USER, 0, "Library: User not found");
        }
        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/loanreturn/searchitem", name="library_libraryitem_loanreturn_searchitem")
     */
    function loanReturnSearchItem(Request $request) {
        $body = '';
        $loan_types = array('GIRIŞ', 'ÇYKYŞ');
        $status_types = array('CHECKED OUT', 'AVAILABLE');
        $item_status = 0;
        $loan_itemcallnumber = $request->request->get("loan_itemcallnumber", 0);
        $loan_usernumber = $request->request->get("loan_usernumber", 0);
        //$body .= $loan_badgeid;
        //$body .= "Request:".$request->request->get("loan_usernumber");
        $itemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $item = $itemRepository->findOneBy(['callNumber' => $loan_itemcallnumber, 'libraryUnit' => $this->libraryUnit]);
        if ($item) {
            $body .= "<table width='95%' align='center' border=0>";
            $body .= "<tr><td align='center' colspan='2'><img src='/build/library/" . $item->getLibraryUnit()->getId() . "/items/photos/" . $item->getCallNumberOriginal() . ".jpg' width='160' class='userphoto'></td></tr>";
            $body .= "<tr><td colspan=2>&nbsp;</td></tr>";
            $body .= "<tr><td width='20%'>UOK:</td><td><span>" . $item->getUok() . "</span></td></tr>";
            $body .= "<tr><td>Title:</td><td><span>" . $item->getMainTitle() . ". " . $item->getSecondaryTitle() . "</span></td></tr>";
            $body .= "<tr><td>Author(s):</td><td><span>" . $item->getAuthor() . "</span></td></tr>";
            $body .= "<tr><td>Year:</td><td><span>" . $item->getYear() . "</span></td></tr>";
            $body .= "<tr><td>Publisher:</td><td><span>" . $item->getPublisher() . "</span></td></tr>";
            $body .= "<tr><td>Place:</td><td><span>" . $item->getPlace() . "</span></td></tr>";
            $body .= "<tr><td>Status:</td><td><span class='status" . $item->getStatus() . "'>" . $status_types[$item->getStatus()] . "</span></td></tr>";
            $body .= "</table>";
            if ($item->getStatus() == 1) {
                $body .= "<script>additem(new Array(1,'" . $item->getCallNumber() . "','" . $item->getMainTitle() . "','" . $item->getAuthor() . "'));</script>";
            } else {
                $loanRepository = $this->getDoctrine()->getRepository(LibraryLoan::class);
                $loan = $loanRepository->findOneBy([
                    "libraryItem" => $item,
                    'libraryUnit' => $this->libraryUnit,
                    "status" => 0
                ]);

                if ($loan) {
                    if ($loan->getUser()->getId() == $loan_usernumber) {
                        $body .= "<script>additem(new Array(0, '" . $loan->getLibraryItem()->getCallNumber() . "','" . $loan->getLibraryItem()->getMainTitle() . "','" . $loan->getLibraryItem()->getAuthor() . "'));</script>";
                    }
                }
            }
        } else {
            $body .= "Item not found!";
        }
        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/loanreturn/loanitem", name="library_libraryitem_loanreturn_loanitem")
     */
    function loanItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $loan_status = 0;
        $loan_types = array('GIRIŞ', 'ÇYKYŞ');
        $status_types = array('CHECKED OUT', 'AVAILABLE');
        $item_status = 0;
        $loan_itemcallnumber = $request->request->get("loan_itemcallnumber");
        $loan_usernumber = $request->request->get("loan_usernumber");
        $body .= "Item:" . $loan_itemcallnumber . "<br>";
        $body .= "User:" . $loan_usernumber . "<br>";

        if (strlen($loan_usernumber) == 0 || strlen($loan_itemcallnumber) == 0) {
            $body .= "Invalid User or Item number! ";
            return new Response($body);
        }
        $itemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $item = $itemRepository->findOneBy(['callNumber' => $loan_itemcallnumber, 'libraryUnit' => $this->libraryUnit]);
        if ($item) {
            $body .= "item:" . $item->getId() . "<br>";
            $loanRepository = $this->getDoctrine()->getRepository(LibraryLoan::class);
            $loan = $loanRepository->findOneBy(["libraryItem" => $item, 'libraryUnit' => $this->libraryUnit, "status" => 0]);
            if ($loan) {
                $body .= "Item is already CHECKED OUT to user: " . $loan->getUser()->getSystemId() . "<br>";
                $body .= "Loan date:" . $loan->getLoanDate()->format('d.m.Y');
                $body .= "Return date:" . $loan->getReturnDate()->format('d.m.Y');
            } else {
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                $user = $userRepository->find($loan_usernumber);
                if ($user) {
                    $loanDate = new \DateTime();
                    $returnDate = clone $loanDate;
                    $returnDate->add(new \DateInterval('P15D'));
                    $newLoan = new LibraryLoan();
                    $newLoan->setUser($user);
                    $newLoan->setLibraryItem($item);
                    $newLoan->setStatus(0);
                    $newLoan->setLoanDate($loanDate);
                    $newLoan->setReturnDate($returnDate);
                    $newLoan->setDateUpdated($loanDate);
                    $newLoan->setUser($user);
                    $newLoan->setAuthor($this->getUser());
                    $newLoan->setLibraryUnit($this->libraryUnit);
                    $loanRepository->save($newLoan);

                    $item->setStatus(0);
                    $itemRepository->update($item);

                    $body .= "Success! Item [" . $loan_itemcallnumber . "] is CHECKED OUT to " . $user->getFullname();
                    $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                            $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, $item->getId(), "Loan success");
                } else {
                    $body .= "User cannot be found.";
                }
            }
        } else {
            $body .= "Item cannot be found";
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, "Item not found");
        }

        return new Response($body);
    }

    /**
     * @Route("/library/libraryitem/loanreturn/returnitem", name="library_libraryitem_loanreturn_returnitem")
     */
    function returnItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $loan_status = 0;
        $loan_itemcallnumber = $request->request->get("loan_itemcallnumber");
        $loan_usernumber = $request->request->get("loan_usernumber");
        $body .= "Item:" . $loan_itemcallnumber . "<br>";
        $body .= "User:" . $loan_usernumber . "<br>";

        if (strlen($loan_usernumber) == 0 || strlen($loan_itemcallnumber) == 0) {
            $body .= "Invalid User or Item number! ";
            return new Response($body);
        }
        $itemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $item = $itemRepository->findOneBy(['callNumber' => $loan_itemcallnumber, 'libraryUnit' => $this->libraryUnit]);
        if ($item) {
            $body .= "item:" . $item->getId() . "<br>";
            $loanRepository = $this->getDoctrine()->getRepository(LibraryLoan::class);
            $loan = $loanRepository->findOneBy(["libraryItem" => $item, 'libraryUnit' => $this->libraryUnit, "status" => 0]);
            if ($loan) {
                $loan->setStatus(1);
                $loan->setReturnDate(new \DateTime());
                $loan->setDateUpdated(new \DateTime());
                $loanRepository->save($loan);

                $item->setStatus(1);
                $itemRepository->save($item);
                $body .= "Success! Item [" . $loan_itemcallnumber . "] is RETURNED by " . $loan_usernumber;
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, $item->getId(), "Return success");
            } else {
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, $item->getId(), "Loan not found");
                $body .= "Loan is not found!";
            }
        } else {
            $body .= "Item cannot be found!";
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, "Item not found");
        }

        return new Response($body);
    }

    /**
     * @Route("/library/user/jsonlist/{term?}", name="library_user_jsonlist")
     */
    public function userJsonList(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $result_array = array();
        $term = $request->attributes->get('term');
        try {
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $users = $userRepository->findLikeTerm($term);
            $usertype = "unknown";
            foreach ($users as $user) {
                switch (strlen($user['system_id'])) {
                    case 4:
                        $usertype = "TEACHER";
                        break;
                    case 6:
                        $usertype = "STUDENT";
                        break;
                    case 3:
                        $usertype = "EMPLOYEE";
                        break;
                }
                $result_array[] = array(
                    "id" => $user['system_id'],
                    "value" => $user['firstname'] . " " . $user['lastname'] . " - " . $user['system_id'] . " - " . $usertype
                );
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
     * @Route("/library/item/jsonlist/{term?}", name="library_item_jsonlist")
     */
    public function itemJsonList(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $result_array = array();
        $term = $request->attributes->get('term');
        try {
            $libraryItemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
            $libraryItems = $libraryItemRepository->findLikeByField(
                    [
                        'libraryUnitId' => $this->libraryUnit->getSystemId(),
                        'table' => 'library_item',
                        'field' => 'main_title',
                        'value' => $term,
            ]);
            $itemStatus = "IN";
            foreach ($libraryItems as $libraryItem) {
                if ($libraryItem->getStatus() == 1) {
                    $itemStatus = "IN";
                } else {
                    $itemStatus = "OUT";
                }
                $result_array[] = array(
                    "id" => $libraryItem->getCallNumber(),
                    "value" => $libraryItem->getMainTitle() . " - " . $libraryItem->getAuthor() . " - " . $libraryItem->getCallNumber() . " - " . $itemStatus
                );
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
     * @Route("/library/libraryitem/ebookview2", name="library_item_ebookview2")
     */
    public function ebookView(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_USER");
//        $result = '';
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, "http://campus3/library/libraryitem/ebookview?file=Arbitrazh_ish_yoeredish.pdf");
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $out = curl_exec($ch);
//        curl_close($ch);
//        $result .= $out;
//        return new Response($result);
//        //$request->attributes->set('mykey', 'myvalue');
//        $hostname = $_SERVER['SERVER_NAME'];
//        return new RedirectResponse("http://".$_SERVER['SERVER_NAME']."/library/libraryitem/ebookviewreal?file=Arbitrazh_ish_yoeredish.pdf");
    }

    /**
     * @Route("/library/libraryitem/bookcard/{libraryUnit}/{itemCallNumber?000001}/{count?1}", name="library_item_bookcard")
     */
    public function book_card(Request $request) {
        $libraryUnit = $id = $request->attributes->get('libraryUnit');
        $itemCallNumber = $id = $request->attributes->get('itemCallNumber');
        $count = $id = $request->attributes->get('count');

        $libraryItems = [];
        $libraryItemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $beginNumber = intval($itemCallNumber);
        for ($i = 0; $i < $count; $i++) {
            $callNumber = str_pad($beginNumber + $i, 6, "0", STR_PAD_LEFT);
            $libraryItem = $libraryItemRepository->findOneBy(['callNumber' => $callNumber, 'libraryUnit' => $libraryUnit]);
            if ($libraryItem) {
                $libraryItems[] = $libraryItem;
            }
        }

        $content = '';
        $generator = new BarcodeGeneratorHTML();
        foreach ($libraryItems as $libraryItem) {
            $content .= "<div class='itemCell'>";
            //$content .= "<div class='libraryname'>IUHD LIBRARY</div>";
            $content .= "<div class='booktitle'>" . $libraryItem->getUok() . "</div>";
            $content .= $generator->getBarcode($libraryItem->getCallNumber(), $generator::TYPE_CODE_128, 1.5, 40);
            $content .= "<div class='code'>IUHD " . $libraryItem->getCallNumber() . "</div>";
            $content .= "</div>";
        }

        return $this->render('library/bookcard.html.twig', [
                    'controller_name' => 'LibraryItemController',
                    'content' => $content
        ]);
    }

}
