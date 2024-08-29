<?php

namespace App\Library\Controller;

use App\Library\Entity\LibraryItem;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryLoan;
use App\Entity\Language;
use App\Library\Form\LibraryLoanFormType;
use App\Library\Service\LibraryLoanManager;
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
use App\Repository\SettingRepository;

class LibraryLoanController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;

    function __construct(LibraryLoanManager $manager, SystemEventManager $systemEventManager,
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
     * @Route("/library/libraryloan", name="library_libraryloan")
     * @Route("/library/libraryloan/search/{searchField?}/{searchValue?}", name="library_libraryloan_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, $searchField . "=" . $searchValue);
        return $this->render('library/libraryloan.html.twig', [
                    'controller_name' => 'LibraryLoanController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/libraryloan/list", name="library_libraryloan_list")
     * @Route("/library/libraryloan/list/{offset?0}/{pageSize?20}/{sorting?library_loan.id}/{searchField?}/{searchValue?}", name="library_libraryloansearch_list")
     */
    public function list(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            //echo "Library unit:".$request->request->get('library_unit_id');
            $params = [
                'table' => 'library_loan',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue'),
                'libraryUnitId' => $this->libraryUnit->getId()
            ];
            $repository = $this->getDoctrine()->getRepository(LibraryLoan::class);
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
     * @Route("/library/libraryloan/create", name="library_libraryloan_create")
     */
    public function create(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $item = $this->manager->createFromRequest($request, $language, $this->libraryUnit);
            $repository = $this->getDoctrine()->getRepository(LibraryLoan::class);
            $repository->save($item);
            //*/
            $result = $repository->getLastInserted(['table' => 'library_loan', 'libraryUnitId' => $this->libraryUnit->getId()]);

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
     * @Route("/library/libraryloan/update", name="library_libraryloan_update")
     */
    public function update(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $languageId = $request->request->get('language_id');
            $languageRepository = $this->getDoctrine()->getRepository(Language::class);
            $language = $languageRepository->find($languageId);

            $id = $request->request->get('id');
            $repository = $this->getDoctrine()->getRepository(LibraryLoan::class);
            $item = $repository->find($id);

            $updatedItem = $this->manager->updateFromRequest($request, $item, $language, $this->libraryUnit);

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
     * @Route("/library/libraryloan/delete/{id?}", name="library_libraryloan_delete")
     */
    public function delete(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        try {
            $id = $request->request->get('id');
            if (empty($id)) {
                $id = $request->attributes->get('id');
            }
            $repository = $this->getDoctrine()->getRepository(LibraryLoan::class);
            $item = $repository->find($id);
            if ($this->libraryUnit != $item->getLibraryUnit()) {
                $result_array = [
                    'Result' => "ERROR",
                    'Message' => "This item does not belong in your library"
                ];
            } else {
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

}
