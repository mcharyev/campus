<?php

namespace App\Library\Controller;

use App\Library\Entity\Publication;
use App\Entity\User;
use App\Library\Service\PublicationManager;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Library\Repository\LibraryUnitRepository;
use App\Library\Entity\LibraryUnit;

class PublicationController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $systemInfoManager;
    private $libraryUnit;
    private $uploadPath;

    function __construct(PublicationManager $manager, SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository, SystemInfoManager $systemInfoManager) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;

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

        $this->uploadPath = $this->systemInfoManager->getRootPath() . "/var/uploads/publications/";
    }

    /**
     * @Route("/library/publication", name="library_publication")
     * @Route("/library/publication/search/{searchField?}/{searchValue?}", name="library_publication_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                $this->getUser()->getId(), EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $searchField . "=" . $searchValue);
        return $this->render('library/publication.html.twig', [
                    'controller_name' => 'PublicationController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/publication/list", name="library_publication_list")
     * @Route("/library/publication/list/{offset?0}/{pageSize?20}/{sorting?library_item.id}/{searchField?}/{searchValue?}", name="library_publicationsearch_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            //echo "Library unit:".$request->request->get('library_unit_id');
            $params = [
                'table' => 'publication',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(Publication::class);
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
     * @Route("/library/publication/create", name="library_publication_create")
     */
    public function create(Request $request, Security $security) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $user = $this->getDoctrine()->getRepository(User::class)->find($request->request->get('author_id'));
            $recorder = $security->getUser();

            $item = $this->manager->createFromRequest($request, $user, $recorder);
            $repository = $this->getDoctrine()->getRepository(Publication::class);
            $repository->save($item);
            //*/
            $result = $repository->getLastInserted(['table' => 'publication']);

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
     * @Route("/library/publication/update", name="library_publication_update")
     */
    public function update(Request $request, Security $security) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $user = $this->getDoctrine()->getRepository(User::class)->find($request->request->get('author_id'));
            $recorder = $security->getUser();

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(Publication::class);
            $item = $repository->find($id);

            $updatedItem = $this->manager->updateFromRequest($request, $item, $user, $recorder);

            $repository->update($updatedItem);
            $result_array = [
                'Result' => "OK",
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
     * @Route("/library/publication/delete/{id?}", name="library_publication_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(Publication::class);
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
     * @Route("/library/publication/listitemfile", name="library_publication_listitemfile")
     */
    public function listItemFile(Request $request) {
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Publication::class);
        $item = $repository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($item) {
            $file = $item->getFile();
            if ($file) {
                if (file_exists($this->uploadPath . $file)) {
                    $body .= "<a target=\"_blank\" href=\"/library/publication/view/?file=publications|" . $item->getFile() . "\">" . $file . "</a><br><br>";
                } else {
                    $body .= "Faýl tapylmady: " . $file . "<br>";
                }
            } else {
                $body .= "Faýl bellenmändir<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/library/publication/uploaditemfile", name="library_publication_uploaditemfile")
     */
    function uploadItemFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $allowed = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG');
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $body .= 'Faýlyň bu görnüşine rugsat berilmeýär.';
            return new Response($body);
        }

        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Publication::class);
        $item = $repository->find($id);
        if ($item) {
            $filename = $_FILES['file']['name'];
            $uploadfile = $this->uploadPath . $filename;
            if (!file_exists($uploadfile)) {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                    $item->setFile($filename);
                    $repository->save($item);
                    $body .= "Faýl ýüklendi: " . $filename . "\n";
                } else {
                    $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
                }
            } else {
                $body .= "Şeýle ada eýe faýl öňem bar! Başga at saýlaň\n";
            }
        } else {
            $body .= "Maglumat tapylmady!\n";
        }
        return new Response($body);
    }

    /**
     * @Route("/library/publication/deleteitemfile", name="library_publication_deleteitemfile")
     */
    public function deleteFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $body = '';
        $id = $request->request->get('id', 0);
        $repository = $this->getDoctrine()->getRepository(Publication::class);
        $item = $repository->find($id);
        if ($item) {
            $filename = $item->getFile();
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
     * @Route("/library/publication/view/{file?blank.png}", name="library_publication_view")
     */
    public function ebookView(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
//        $viewer = "ebookviewer.html.twig";
//        return $this->render('library/' . $viewer, [
//                    'controller_name' => 'PublicationController'
//        ]);
        $file = $request->attributes->get('file');
        return new RedirectResponse("/fileserver/get/publications|".$file);
    }

}
