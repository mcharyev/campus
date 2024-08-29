<?php

namespace App\Library\Controller;

use App\Library\Entity\LibraryItem;
use App\Library\Entity\LibraryUnit;
use App\Library\Entity\LibraryLoan;
use App\Entity\Language;
use App\Library\Form\LibraryItemFormType;
use App\Library\Service\LibraryItemManager;
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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Library\Repository\LibraryUnitRepository;
use App\Entity\User;
use App\Entity\EnrolledStudent;
use App\Entity\Teacher;
use App\Hr\Entity\Employee;
use App\Entity\InformationText;
use App\Utility\PDFDocument;
use App\Utility\ImageFile;

class EbookBrowserController extends AbstractController {

    private $manager;
    private $systemEventManager;
    private $systemInfoManager;
    private $itemsFolder;
    private $itemsFolderOriginal;
    private $libraryUnit;
    private $turkmenChars;
    private $latinChars;

    function __construct(LibraryItemManager $manager, SystemEventManager $systemEventManager,
            Security $security, LibraryUnitRepository $libraryUnitRepository, SystemInfoManager $systemInfoManager) {
        $this->manager = $manager;
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->turkmenChars = array(" ", "", "Ý", "ý", "Ç", "ç", "Ä", "ä", "Ň", "ň", "Ö", "ö", "Ü", "ü", "Ž", "ž", "Ş", "ş");
        $this->latinChars = array("_", ".pdf", "Y0", "y0", "C0", "c0", "A0", "a0", "N0", "n0", "O0", "o0", "U0", "u0", "Z0", "z0", "S0", "s0");
    }

    /**
     * @Route("/ebookbrowser/browse/{libraryUnitSystemId?1}/{path?ebooks}", name="ebookbrowser_index")
     * @Route("/ebookbrowser/browse/libraryitem/{libraryUnitSystemId?1}", name="ebookbrowser_libraryitem")
     * @Route("/ebookbrowser/browse/libraryitem/search/{libraryUnitSystemId?1}/{searchField?}/{searchValue?}", name="ebookbrowser_libraryitem_search")
     */
    public function index(Request $request) {
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $content = "";
        $originPath = $request->attributes->get('path');
        if ($originPath) {
            $path = str_replace("|", "\\", $originPath);
            $this->updateLibraryUnit($libraryUnitSystemId);
            $currentPath = $this->systemInfoManager->getRootPath() . "/var/uploads/" . $path;
            $content .= "<div class='path'>Category: " . $this->generatePathString($originPath) . "</div><br>";
            if ($this->isGranted("ROLE_ADMIN")) {
                $content .= "<div class='path'>Actions: <a href='/ebookbrowser/generatecovers/" . $libraryUnitSystemId . "/" . $originPath . "'>Generate Covers</a></div>";
            }

            $content .= $this->listFolderFiles($currentPath, $originPath);
        } else {
            $content = "Unknown path!";
        }
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('library/ebookbrowser.html.twig', [
                    'controller_name' => 'EbookBrowserController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'libraryUnit' => $this->libraryUnit,
                    'content' => $content,
        ]);
    }

    function generatePathString($strPath) {
        $result = "";
        $levels = explode("|", $strPath);
        $currentLevel = "";
        $id = $this->libraryUnit->getId();
        $lastItem = sizeof($levels);
        $i = 0;
        foreach ($levels as $level) {
            if (strlen($level) > 0) {
                if ($i > 0) {
                    $currentLevel .= "|" . $level;
                } else {
                    $currentLevel .= $level;
                }
                $result .= "<a href='/ebookbrowser/browse/" . $id . "/" . $currentLevel . "'>" . $this->delatinize($level) . "</a> &gt; ";
            }
            $i++;
        }
        return $result;
    }

    function listFolderFiles($dir, $originPath) {
        $result = "";
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return;

        $isAdmin = $this->isGranted("ROLE_ADMIN");

        $result .= "<div class='folders'>";
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff)) {
                $itemCounts = $this->getItemCounts($dir . '/' . $ff);
                //$result .= "<div class='item folder'>";
                //$result .= "<div class='itempicture' style='background-image: url(\"/build/images/bookshelf.jpg\");' onclick='opendir(\"" . $originPath . "|" . $ff . "\");'></div>";
                $result .= "<div class='folderitemname'><i class=\"fa fa-book\" aria-hidden=\"true\"></i> <a href='javascript:opendir(\"" . $originPath . "|" . $ff . "\");'>" . $this->delatinize($ff) . "</a> [" . $itemCounts[0] . ", " . $itemCounts[1] . "] </div>";
                //$result .= "</div>";
            }
        }
        $result .= "</div>";
        $result .= "<div class='files'>";
        foreach ($ffs as $ff) {
            $filePath = $dir . '/' . $ff;
            $pathinfo = pathinfo($filePath);
            $filename = $pathinfo['filename'];
            $fullname = $pathinfo['basename'];
            if (is_file($filePath) && $pathinfo['extension'] == 'pdf') {
                $bookCover = "/fileserver/get/" . $originPath . "|" . $filename . "_1.jpg";
                $result .= "<div class='item file'>";
                $result .= "<div class='itempicture' style='background-image: url(\"" . $bookCover . "\");' onclick='openbook(\"" . $originPath . "|" . $fullname . "\");'></div>";
                $result .= "<div class='itemname'><a href='javascript:openbook(\"" . $originPath . "|" . $fullname . "\");'>" . $this->delatinize($ff) . "</a>";
                if ($isAdmin) {
                    $result .= " <a href='javascript:deleteitem(\"" . $originPath . "|" . $fullname . "\");'>Delete</a>";
                }
                $result .= "</div>";
                $result .= "</div>";
            }
        }

        $result .= '</div>';
        return $result;
    }

    /**
     * @Route("/ebookbrowser/deleteitem/{libraryUnitSystemId?1}/{path?ebooks}", name="ebookbrowser_deleteitem")
     */
    public function deleteItem(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $originPath = $request->attributes->get('path');
        $result = '';
        if ($originPath) {
            $filePath = str_replace("|", "\\", $originPath);
            $currentPath = $this->systemInfoManager->getRootPath() . "/var/uploads/" . $filePath;
            $pathinfo = pathinfo($currentPath);
            $filename = $pathinfo['filename'];
            $fullname = $pathinfo['basename'];
            $dirname = $pathinfo['dirname'];
            $bookCoverPath = $dirname . "/" . $pathinfo['filename'] . "_1.jpg";
            unlink($currentPath);
            unlink($bookCoverPath);
            $result .= "File deleted " . $filePath . "!<br>Cover deleted " . $bookCoverPath;
        } else {
            $result .= "Unknown path!";
        }

        return new Response($result);
    }

    private function getItemCounts($dir) {
        $result = [0, 0];
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1) {
            return $result;
        }


        $folderCount = 0;
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff)) {
                $folderCount++;
            }
        }

        $fileCount = 0;
        foreach ($ffs as $ff) {
            $filePath = $dir . '/' . $ff;
            $pathinfo = pathinfo($filePath);
            if (is_file($filePath) && $pathinfo['extension'] == 'pdf') {
                $fileCount++;
            }
        }

        return [$folderCount, $fileCount];
    }

    /**
     * @Route("/ebookbrowser/generatecovers/{libraryUnitSystemId?1}/{path?ebooks}", name="ebookbrowser_generatecovers")
     */
    public function generateCovers(Request $request) {
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
        //$this->denyAccessUnlessGranted("ROLE_LIBRARY");
        $originPath = $request->attributes->get('path');
        $result = '';
        if ($originPath) {
            $path = str_replace("|", "\\", $originPath);
            $currentPath = $this->systemInfoManager->getRootPath() . "/var/uploads/" . $path;
        } else {
            $result .= "Unknown path!";
        }

        $dir = $currentPath;
        $result .= "Generating covers for: " . $dir . "<br>";
        $ffs = scandir($dir);
        $pdfDocument = new PDFDocument();
        $imageFile = new ImageFile();

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1) {
            $result .= "Folder is empty!";
        }
        //$result .= "<div class='items'>";
        foreach ($ffs as $ff) {
            $filePath = $dir . '/' . $ff;
            $pathinfo = pathinfo($filePath);
            $filename = $pathinfo['filename'];
            if (is_file($filePath) && $pathinfo['extension'] == 'pdf') {
                $bookCoverPath = $dir . '/' . $filename . "_1.jpg";
                if (!file_exists($bookCoverPath)) {
                    $resultArray = $pdfDocument->create_preview($filePath, "1", "jpeg", "4", "72");
                    $imageFile->resizeImage($bookCoverPath, 170, 250);
                    $result .= $resultArray['error'] . ": " . $resultArray['message'] . "<br>";
                } else {
                    $result .= "Cover exists<br>";
                }
            }
        }

        //$result .= '</div>';
        $content = $result;

        return $this->render('library/ebookbrowser.html.twig', [
                    'controller_name' => 'EbookBrowserController',
                    'libraryUnit' => $this->libraryUnit,
                    'content' => $content,
        ]);
    }

    private function updateLibraryUnit($libraryUnitSystemId) {
        $this->libraryUnit = $this->getDoctrine()->getRepository(LibraryUnit::class)->findOneBy(['systemId' => $libraryUnitSystemId]);
        return true;
    }

    private function delatinize($str) {
        return str_replace($this->latinChars, $this->turkmenChars, $str);
    }

    function latinize($str) {
        //$result = str_replace($this->turkmenChars, $this->latinChars, $str);
        return str_replace($this->turkmenChars, $this->latinChars, $str);
    }

    /**
     * @Route("/ebookbrowser/ebookview/{libraryUnitSystemId?1}", name="ebookbrowser_ebookview")
     */
    public function ebookView(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $libraryUnitSystemId = $request->attributes->get('libraryUnitSystemId');
        $this->updateLibraryUnit($libraryUnitSystemId);
        $viewer = "ebookviewer.html.twig";
        return $this->render('library/' . $viewer, [
                    'controller_name' => 'LibraryItemController',
                    'libraryUnit' => $this->libraryUnit,
        ]);
    }

}
