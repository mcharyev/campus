<?php

namespace App\Library\Controller;

use App\Library\Entity\LibraryItem;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryLoan;
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

class ReportController extends AbstractController {

    private $systemEventManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;
    private $settingsRepository;

    function __construct(SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository, SettingRepository $settingRepository) {
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
     * @Route("/library/report", name="library_report")
     * @Route("/library/report/topreaders", name="library_report_topreaders")
     */
    public function topReaders(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $content = '';
        $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
        $content .= "<thead><tr>";
        $content .= "<th>Name</th>";
        $content .= "<th>Total loans</th>";
        $content .= "</tr></thead><tbody>";
        $results = $this->getDoctrine()->getRepository(LibraryLoan::class)->getTopReaders(['libraryUnitId' => $this->libraryUnit->getId()]);
        foreach ($results as $row) {
            $content .= "<tr>";
            $content .= "<td>" . $row['loan_username'] . "</td>";
            $content .= "<td>" . $row['total_loans'] . "</td>";
            $content .= "</tr>";
        }
        $content .= "</tbody></table>";


        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_NULL, 0, 'Library report');
        return $this->render('library/report.html.twig', [
                    'controller_name' => 'ReportController',
                    'title' => 'READERS WITH HIGHEST COUNT OF LOANS',
                    'content' => $content,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/report/topitems", name="library_report_topitems")
     */
    public function topItems(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $content = '';
        $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
        $content .= "<thead><tr>";
        $content .= "<th>Name</th>";
        $content .= "<th>Total loans</th>";
        $content .= "</tr></thead><tbody>";
        $results = $this->getDoctrine()->getRepository(LibraryLoan::class)->getTopItems(['libraryUnitId' => $this->libraryUnit->getId()]);
        foreach ($results as $row) {
            $content .= "<tr>";
            $content .= "<td>" . $row['loan_title'] . "</td>";
            $content .= "<td>" . $row['total_loans'] . "</td>";
            $content .= "</tr>";
        }
        $content .= "</tbody></table>";


        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_NULL, 0, 'Library report');
        return $this->render('library/report.html.twig', [
                    'controller_name' => 'ReportController',
                    'title' => 'THE MOST BORROWED ITEMS',
                    'content' => $content,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

    /**
     * @Route("/library/report/loans", name="library_report_loans")
     */
    public function allLoans(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_LIBRARY");

        $libraryRepository = $this->getDoctrine()->getRepository(LibraryUnit::class);
        $libraryItemRepository = $this->getDoctrine()->getRepository(LibraryItem::class);
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $loanRepository = $this->getDoctrine()->getRepository(LibraryLoan::class);

        $content = '';
        $content .= "<table class='table table-bordered table-sm table-striped small' id='mainTable'>";
        $content .= "<thead><tr>";
        $content .= "<th>ID</th>";
        $content .= "<th>Borrower System ID</th>";
        $content .= "<th>Borrower Fullname</th>";
        $content .= "<th>Item Title</th>";
        $content .= "<th>Item Call No.</th>";
        $content .= "<th>Loan date</th>";
        $content .= "<th>Return date</th>";
        $content .= "<th>Library</th>";
        $content .= "<th>Librarian</th>";
        $content .= "<th>Status</th>";
        $content .= "</tr></thead><tbody>";
        $loans = $loanRepository->findAll();
        foreach ($loans as $loan) {
            $content .= "<tr>";
            $content .= "<td>" . $loan->getId() . "</td>";
            $content .= "<td>" . $loan->getUser()->getSystemId() . "</td>";
            $content .= "<td>" . $loan->getUser()->getFullname() . "</td>";
            $content .= "<td>" . $loan->getLibraryItem()->getMainTitle() . "</td>";
            $content .= "<td>" . $loan->getLibraryItem()->getCallNumber() . "</td>";
            $content .= "<td>" . $loan->getLoanDate()->format('Y-m-d H:i:s') . "</td>";
            $content .= "<td>" . $loan->getReturnDate()->format('Y-m-d H:i:s') . "</td>";
            $content .= "<td>" . $loan->getLibraryUnit()->getNameEnglish() . "</td>";
            $content .= "<td>" . $loan->getAuthor()->getFullname() . "</td>";
            $content .= "<td>" . $loan->getStatus() . "</td>";
            $content .= "</tr>";
        }
        $content .= "</tbody></table>";


        $this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_VIEW, EntityTypeEnum::ENTITY_USER, $this->getUser()->getId(), EntityTypeEnum::ENTITY_NULL, 0, 'Library report');
        return $this->render('library/report.html.twig', [
                    'controller_name' => 'ReportController',
                    'title' => 'Loans from the library',
                    'content' => $content,
                    'libraryUnit' => $this->libraryUnit
        ]);
    }

}
