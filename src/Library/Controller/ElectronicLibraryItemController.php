<?php

namespace App\Library\Controller;

use App\Library\Entity\ElectronicLibraryItem;
use App\Entity\Language;
use App\Library\Service\ElectronicLibraryItemManager;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Library\Repository\LibraryUnitRepository;
use App\Library\Entity\LibraryUnit;
use App\Repository\SettingRepository;

class ElectronicLibraryItemController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $systemInfoManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;
    private $uploadPath;
    private $settingsRepository;

    function __construct(ElectronicLibraryItemManager $manager, SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository, SystemInfoManager $systemInfoManager, SettingRepository $settingRepository) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
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
        $this->itemsFolder = $rootPath . "/public/build/library/" . $libraryUnitId . "/electronicitems/photos/";
        $this->itemsFolderOriginal = $rootPath . "/public/build/library/" . $libraryUnitId . "/electronicitems/photos_original/";
        $this->uploadPath = $rootPath . "/var/uploads/elibrary/" . $libraryUnitId . "/";
    }

    /**
     * @Route("/library/electroniclibraryitem", name="library_electroniclibraryitem")
     * @Route("/library/electroniclibraryitem/search/{searchField?}/{searchValue?}", name="library_electroniclibraryitem_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $searchField . "=" . $searchValue);
        return $this->render('library/electroniclibraryitem.html.twig', [
                    'controller_name' => 'ElectronicLibraryItemController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/electroniclibraryitem/list", name="library_electroniclibraryitem_list")
     * @Route("/library/electroniclibraryitem/list/{offset?0}/{pageSize?20}/{sorting?library_item.id}/{searchField?}/{searchValue?}", name="library_electroniclibraryitemsearch_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            //echo "Library unit:".$request->request->get('library_unit_id');
            $params = [
                'table' => 'electronic_library_item',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'libraryUnitId' => $this->libraryUnit->getId()
            ];
            $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
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
     * @Route("/library/electroniclibraryitem/create", name="library_electroniclibraryitem_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $item = $this->manager->createFromRequest($request, $language, $this->libraryUnit);
            $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
            $repository->save($item);
            //*/
            $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_CREATE, EntityTypeEnum::ENTITY_USER,
                    $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $item->getMainTitle());
            $result = $repository->getLastInserted(['table' => 'electronic_library_item', 'libraryUnitId' => $this->libraryUnit->getId()]);

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
     * @Route("/library/electroniclibraryitem/update", name="library_electroniclibraryitem_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
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
     * @Route("/library/electroniclibraryitem/delete/{id?}", name="library_electroniclibraryitem_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
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
     * @Route("/library/electroniclibraryitem/listitemphoto", name="library_electroniclibraryitem_listitemphoto")
     */
    public function listItemPhoto(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
        $libraryItem = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($libraryItem) {
            if (strlen($libraryItem->getCallNumberOriginal()) == 0)
                $file = $libraryItem->getCallNumber() . ".jpg";
            else
                $file = $libraryItem->getCallNumberOriginal() . ".jpg";
            //echo ITEMS_DIR.$file;
            if (file_exists($this->itemsFolder . $file)) {
                $body .= "<a href='/build/library/" . $this->libraryUnit->getId() . "/electronicitems/photos/" . $file . "'>" . $file . "</a><br><br>";
            } else {
                $body .= "Faýl tapylmady: " . $file . "<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/library/electroniclibraryitem/uploaditemphoto", name="library_electroniclibraryitem_uploaditemphoto")
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
            $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
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
     * @Route("/library/electroniclibraryitem/deleteitemphoto", name="library_electroniclibraryitem_deleteitemphoto")
     */
    public function deletePhoto(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
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
     * @Route("/library/electroniclibraryitem/listitemfile", name="library_electroniclibraryitem_listitemfile")
     */
    public function listItemFile(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
        $libraryItem = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($libraryItem) {
            $file = $libraryItem->getCallNumber() . ".pdf";
            //echo ITEMS_DIR.$file;
            if (file_exists($this->uploadPath . $file)) {
                $body .= "<a target=\"_blank\" href=\"/library/electroniclibraryitem/view/" . $this->libraryUnit->getId() . "/?file=elibrary|" . $this->libraryUnit->getId() . "|" . $libraryItem->getCallNumber() . ".pdf\">" . $file . "</a><br><br>";
            } else {
                $body .= "Faýl tapylmady: " . $file . "<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/library/electroniclibraryitem/uploaditemfile", name="library_electroniclibraryitem_uploaditemfile")
     */
    function uploadItemFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $allowed = array('pdf', 'PDF');
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $body .= 'Faýlyň bu görnüşine rugsat berilmeýär.';
            return new Response($body);
        }

        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
        $libraryItem = $repository->find($id);
        if ($libraryItem) {
            $filename = $libraryItem->getCallNumber() . ".pdf";
            $uploadfile = $this->uploadPath . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
//                $body .= "<br>".$uploadfile;
//                $body .= "<br>".$uploadfileoriginal;
                $body .= "Faýl ýüklendi: " . $filename . "\n";
                $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                        $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, "Upload file: " . $libraryItem->getMainTitle());
            } else {
                $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
            }
//            $body .= 'okay ';
        }
        return new Response($body);
    }

    /**
     * @Route("/library/electroniclibraryitem/deleteitemfile", name="library_electroniclibraryitem_deleteitemfile")
     */
    public function deleteFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(ElectronicLibraryItem::class);
        $libraryItem = $repository->find($id);
        if ($libraryItem) {
            $filename = $libraryItem->getCallNumber() . ".pdf";
            $filePath = $this->uploadPath . $filename;
            try {
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

    /**
     * @Route("/library/electroniclibraryitem/view/{libraryUnitSystemId?1}", name="library_electroniclibraryitem_view")
     */
    public function ebookView(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_USER");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
        $viewer = "ebookviewer.html.twig";
        return $this->render('library/' . $viewer, [
                    'controller_name' => 'ElectronicLibraryItemController',
                    'libraryUnit' => $this->libraryUnit,
        ]);
    }

    private function updateLibraryUnit($libraryUnitSystemId) {
        $this->libraryUnit = $this->getDoctrine()->getRepository(LibraryUnit::class)->findOneBy(['systemId' => $libraryUnitSystemId]);
        return true;
    }

}
