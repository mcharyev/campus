<?php

namespace App\Controller\EDMS;

use App\Entity\ElectronicDocument;
use App\Form\ElectronicDocumentFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Enum\DocumentTypeEnum;
use App\Enum\DocumentEntryTypeEnum;
use App\Enum\DocumentStatusTypeEnum;
use App\Enum\DocumentOriginTypeEnum;
use App\Utility\LocaleConverter;

class ElectronicDocumentController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/edms/electronic_document/{entryType?1}", name="electronic_document")
     * @Route("/edms/electronic_document/search/{entryType?1}/{searchField?}/{searchValue?}", name="electronic_document_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $entryType = $request->attributes->get('entryType');
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');

        $pageTitle = DocumentEntryTypeEnum::getTypeName($entryType);

        return $this->render('electronic_document/index.html.twig', [
                    'controller_name' => 'ElectronicDocumentController',
                    'entry_type' => $entryType,
                    'search_field' => $searchField,
                    'search_value' => $searchValue,
                    'page_title' => $pageTitle,
                    'status_types_array' => DocumentStatusTypeEnum::getJsonArray(),
                    'document_types_array' => DocumentTypeEnum::getJsonArray(),
        ]);
    }

    /**
     * @Route("/edms/electronic_document/list/{entryType?1}/{offset?}/{pageSize?}/{sorting?}", name="electronic_document_list")
     * @Route("/edms/electronic_document/list/{entryType?1}/{offset?0}/{pageSize?20}/{sorting?id}/{searchField?}/{searchValue?}", name="electronic_document_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        try {
            $params = [
                'table' => 'electronic_document',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'entryType' => $request->attributes->get('entryType'),
                'searchField' => $searchField,
                'searchValue' => $searchValue,
                'comparisonOperator' => 'LIKE',
                'comparisonCharacter' => '%',
            ];
            if ($searchField == 'date_sent' || $searchField == 'date_received') {
                $localeConverter = new LocaleConverter();
                $params['searchValue'] = $localeConverter->convertToSqlDate($searchValue);
                $params['comparisonOperator'] = '=';
                $params['comparisonCharacter'] = '';
            } elseif ($searchField == 'number_sent' || $searchField == 'number_received') {
                $params['comparisonOperator'] = '=';
                $params['comparisonCharacter'] = '';
            }

            $repository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
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
     * @Route("/edms/electronic_document/{entryType?1}/delete", name="electronic_document_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $electronicDocument = $entityManager->getRepository(ElectronicDocument::class)->find($id);
            $entityManager->remove($electronicDocument);
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
     * @Route("/edms/electronic_document/new/{entryType?1}", name="electronic_document_new")
     * @Route("/edms/electronic_document/edit/{entryType?1}/{id?0}", name="electronic_document_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $repository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
        $entryType = $request->attributes->get('entryType');
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $electronicDocument = $repository->find($id);
            $editType = 'edit';
        } else {
            $electronicDocument = new ElectronicDocument();
            $editType = 'new';
        }
        $form = $this->createForm(ElectronicDocumentFormType::class, $electronicDocument, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $electronicDocument = $form->getData();
            $fields = [
                'note'
            ];
            foreach ($fields as $field) {
                $electronicDocument->setDataField($field, $form->get($field)->getData());
            }

            $repository->save($electronicDocument);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('electronic_document');
        }
        return $this->render('electronic_document/form.html.twig', [
                    'form' => $form->createView(),
                    'edit_type' => $editType,
                    'entry_type' => $entryType,
        ]);
    }

    /**
     * @Route("/edms/electronic_document/{entryType?1}/uploadfile", name="electronic_document_uploadfile")
     */
    function uploadFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $body = '';
        $allowed = array('pdf', 'xls', 'xlsx', 'doc', 'docx', 'png', 'jpg', 'tif', 'bmp');
        $uploadedFileCount = count($_FILES['file']['name']);

        $id = $request->request->get('id', 0);
        $electronicDocumentRepository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
        $electronicDocument = $electronicDocumentRepository->find($id);
        if ($electronicDocument) {
            $addedFiles = [];
            //$filename = $enrolledStudent->getSystemId() . ".jpg";
            $i = 0;
            $uploadPath = $this->systemInfoManager->getRootPath() . "/var/uploads/electronicdocuments/" . $electronicDocument->getId();
            if (!file_exists($uploadPath)) {
                $dirName = mkdir($uploadPath);
            }
            foreach ($_FILES['file']['name'] as $filename) {
                //$filename = $_FILES['file']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $body .= 'Faýlyň bu görnüşine ' . strtoupper($ext) . ' (' . $filename . ') rugsat berilmeýär.<br>';
                }
                $uploadFile = $uploadPath . "/" . $filename;
                try {
                    if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $uploadFile)) {
                        //$dst = $imageFile->resizeImageHeight($uploadfile, 300);
                        $addedFiles[] = $filename;
                        $body .= "Faýl ýüklendi: " . $filename . "\n";
                        //$this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                        //        $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, "Upload file: " . $enrolledStudent->getSystemId());
                    } else {
                        $body .= "Howply faýl bolmagy ähtimal!\n";
                    }
                } catch (\Exception $e) {
                    $body .= $e->getMessage() . "\n";
                }
                $i++;
            }
            $electronicDocument->addFiles($addedFiles);
            $electronicDocumentRepository->save($electronicDocument);
        }
        return new Response($body);
    }

    /**
     * @Route("/edms/electronic_document/{entryType?1}/listfile", name="electronic_document_listfile")
     */
    public function listFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $body = '';
        $id = $request->request->get('id', 0);
        $electronicDocumentRepository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
        $electronicDocument = $electronicDocumentRepository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($electronicDocument) {
            $files = $electronicDocument->getFiles();
            foreach ($files as $filename) {
                $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/electronicdocuments/" . $filename;
                if (file_exists($filePath)) {
                    $body .= "<a href='/referencedocuments/" . $filename . "'>" . $filename . "</a><br><br>";
                } else {
                    $body .= "Faýl ýok.<br>";
                }
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/edms/electronic_document/deletefile", name="electronic_document_deletefile")
     */
    public function deleteFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $body = '';
        $id = $request->request->get('id', 0);
        $electronicDocumentRepository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
        $electronicDocument = $electronicDocumentRepository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($electronicDocument) {
            $filename = $electronicDocument->getLink();
            if (strlen($filename) > 0) {
                $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/referencedocuments/" . $filename;
                try {
                    if (file_exists($filePath)) {
                        if (unlink($filePath)) {
                            $electronicDocument->setLink('');
                            $electronicDocumentRepository->save($electronicDocument);
                            $body .= "Faýl pozuldy: " . $filename . "<br>";
                        } else {
                            $body .= "Faýl pozulmady: " . $filename . "<br>";
                        }
                    } else {
                        $body .= "Faýl tapylmady: " . $filename . "<br>";
                    }
                } catch (\Exception $e) {
                    $body = $e->getMessage() . " File:" . $e->getFile() . " Line:" . $e->getLine();
                }
            } else {
                $body .= "Faýl ýok<br>";
            }
        } else {
            $body .= "Invalid data: " . $id . "<br>";
        }
        return new Response($body);
    }

    /**
     * @Route("/edms/electronic_document/downloadfile/{id}/{filename}", name="electronic_document_downloadfile")
     */
    public function downloadFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_EDMS_MANAGER");
        $body = '';
        $id = $request->attributes->get('id', 0);
        $filename = $request->attributes->get('filename');

//        $electronicDocumentRepository = $this->getDoctrine()->getRepository(ElectronicDocument::class);
//        $electronicDocument = $electronicDocumentRepository->find($id);
//        //$body .= "Item call number:" . $item_callnumber;
//        if ($electronicDocument) {
        if (strlen($filename) > 0) {
            //$filename = str_replace("|", "/", $filename);
            $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/electronicdocuments/" . $id . "/" . $filename;
            try {
                if (file_exists($filePath)) {
                    $response = new BinaryFileResponse($filePath);
                    $response->setCache([
                        'public' => true,
                        'private' => false,
                        'max_age' => 31536000,
                        's_maxage' => 31536000
                    ]);
                    $response->setContentDisposition(
//                                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                            ResponseHeaderBag::DISPOSITION_INLINE,
                            $filename
                    );
                    return $response;
                } else {
                    $body .= "Faýl tapylmady: " . $filename . "<br>";
                }
            } catch (\Exception $e) {
                $body = $e->getMessage() . " File:" . $e->getFile() . " Line:" . $e->getLine();
            }
        } else {
            $body .= "Faýl ýok<br>";
        }
//        } else {
//            $body .= "Invalid data: " . $id . "<br>";
//        }
        return new Response($body);
    }

}
