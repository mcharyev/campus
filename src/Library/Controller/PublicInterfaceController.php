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
use App\Entity\InformationText;

class PublicInterfaceController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;

    function __construct(LibraryItemManager $manager, SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        //$libraryUnitId = 1;
        //$this->libraryUnit = $libraryUnitRepository->find($libraryUnitId);
    }

    /**
     * @Route("/publiclibrary/{libraryUnitSystemId?}", name="publiclibrary_index")
     * @Route("/publiclibrary/libraryitem/{libraryUnitSystemId?}", name="publiclibrary_libraryitem")
     * @Route("/publiclibrary/libraryitem/search/{libraryUnitSystemId}/{searchField?}/{searchValue?}", name="publiclibrary_libraryitem_search")
     */
    public function index(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                0, EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $searchField . "=" . $searchValue);
        return $this->render('library/publiclibraryitem.html.twig', [
                    'controller_name' => 'PublicInterfaceController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/publiclibrary/libraryitem/list/{libraryUnitSystemId}", name="publiclibrary_libraryitem_list")
     * @Route("/publiclibrary/libraryitem/list/{libraryUnitSystemId}/{offset?0}/{pageSize?20}/{sorting?library_item.id}/{searchField?}/{searchValue?}", name="publiclibrary_libraryitemsearch_list")
     */
    public function list(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
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
     * @Route("/publiclibrary/electronic/{libraryUnitSystemId?}", name="publiclibrary_electronic_index")
     * @Route("/publiclibrary/electroniclibraryitem/{libraryUnitSystemId?}", name="publiclibrary_electroniclibraryitem")
     * @Route("/publiclibrary/electroniclibraryitem/search/{libraryUnitSystemId}/{searchField?}/{searchValue?}", name="publiclibrary_electroniclibraryitem_search")
     */
    public function electronicIndex(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER,
                0, EntityTypeEnum::ENTITY_LIBRARYITEM, 0, $searchField . "=" . $searchValue);
        return $this->render('library/publicelectroniclibraryitem.html.twig', [
                    'controller_name' => 'PublicInterfaceController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/publiclibrary/electroniclibraryitem/list/{libraryUnitSystemId}", name="publiclibrary_electroniclibraryitem_list")
     * @Route("/publiclibrary/electroniclibraryitem/list/{libraryUnitSystemId}/{offset?0}/{pageSize?20}/{sorting?library_item.id}/{searchField?}/{searchValue?}", name="publiclibrary_electroniclibraryitemsearch_list")
     */
    public function electronicList(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
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
     * @Route("/publiclibrary/informationView/{libraryUnitSystemId}/{letterCode?0}", name="public_library_information_view")
     */
    public function informationView(Request $request) {
        $repository = $this->getDoctrine()->getRepository(InformationText::class);
        $letterCode = $request->attributes->get('letterCode');
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
        $informationText = $repository->findOneBy(['letterCode' => $letterCode]);
        if ($informationText) {
            //echo $informationText->getTitle();
            return $this->render('library/information_view.html.twig', [
                        'controller_name' => 'PublicInterfaceController',
                        'libraryUnit' => $this->libraryUnit,
                        'informationText' => $informationText
            ]);
        } else {
            return new Response('Data not found!');
        }
    }

    private function updateLibraryUnit($libraryUnitSystemId) {
        $this->libraryUnit = $this->getDoctrine()->getRepository(LibraryUnit::class)->findOneBy(['systemId' => $libraryUnitSystemId]);
        return true;
    }

}
