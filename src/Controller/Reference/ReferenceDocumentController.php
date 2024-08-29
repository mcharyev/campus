<?php

namespace App\Controller\Reference;

use App\Entity\ReferenceDocument;
use App\Form\ReferenceDocumentFormType;
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

class ReferenceDocumentController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/reference_document", name="reference_document")
     * @Route("/reference_document/search/{searchField?}/{searchValue?}", name="reference_document_search")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $searchField = $request->attributes->get('searchField');
        $searchValue = $request->attributes->get('searchValue');
        return $this->render('reference_document/index.html.twig', [
                    'controller_name' => 'ReferenceDocumentController',
                    'search_field' => $searchField,
                    'search_value' => $searchValue
        ]);
    }

    /**
     * @Route("/reference_document/list/{offset?}/{pageSize?}/{sorting?}", name="reference_document_list")
     * @Route("/reference_document/list/{offset?0}/{pageSize?20}/{sorting?id}/{searchField?}/{searchValue?}", name="reference_document_list_search")
     */
    public function list(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $startIndex = $request->attributes->get('startIndex');
        $pageSize = $request->attributes->get('pageSize');
        $sorting = $request->attributes->get('sorting');

        try {
            $params = [
                'table' => 'reference_document',
                'offset' => $request->attributes->get('offset'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            $repository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
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
     * @Route("/reference_document/delete", name="reference_document_delete")
     */
    public function delete(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            //throw new \Exception('Value of id is: '.json_encode($id));
            $referenceDocument = $entityManager->getRepository(ReferenceDocument::class)->find($id);
            $entityManager->remove($referenceDocument);
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
     * @Route("/reference_document/new", name="reference_document_new")
     * @Route("/reference_document/edit/{id?0}", name="reference_document_edit")
     */
    public function new(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $repository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $id = $request->attributes->get('id');
        if (!empty($id)) {
            $referenceDocument = $repository->find($id);
        } else {
            $referenceDocument = new ReferenceDocument();
        }
        $form = $this->createForm(ReferenceDocumentFormType::class, $referenceDocument, [
            'source_path' => urlencode($request->server->get('HTTP_REFERER'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $referenceDocument = $form->getData();
            $referenceDocument->setDateCreated(new \DateTime());
            $repository->save($referenceDocument);
            $sourcePath = urldecode($form->get('source_path')->getData());
            if (!empty($sourcePath)) {
                return new RedirectResponse($sourcePath);
            }

            return $this->redirectToRoute('reference_document');
        }
        return $this->render('reference_document/form.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reference_document/public_list", name="reference_document_public_list")
     */
    public function publicList(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $repository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $documents = $repository->findAll();
        $documentTypes = [
            '0' => 'Başga',
            '10' => 'Türkmenistanyň Kanuny',
            '20' => 'Düzgünnama',
            '30' => 'Tertip',
            '40' => 'Buýruk',
            '50' => 'Gözükdiriji',
            '60' => 'Içerki düzgünnama',
            '70' => 'Içerki buýruk',
            '80' => 'Döwlet standarty'
        ];
        return $this->render('reference_document/list.html.twig', [
                    'controller_name' => 'ReferenceDocumentController',
                    'documents' => $documents,
                    'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * @Route("/reference_document/uploadfile", name="reference_document_uploadfile")
     */
    function uploadFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $body = '';
        $allowed = array('pdf','doc','docx');
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $body .= 'Faýlyň bu görnüşine '.$ext.' rugsat berilmeýär.';
            return new Response($body);
        }

        $id = $request->request->get('id', 0);
        $referenceDocumentRepository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $referenceDocument = $referenceDocumentRepository->find($id);
        if ($referenceDocument) {
            //$filename = $enrolledStudent->getSystemId() . ".jpg";
            $uploadfile = $this->systemInfoManager->getRootPath() . "/var/uploads/referencedocuments/" . $filename;
            try {
                //$body .= "<br>".$uploadfile;
                if (strlen($referenceDocument->getLink()) == 0) {
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
//                $body .= "<br>".$uploadfile;
//                $body .= "<br>".$uploadfileoriginal;
                        //$dst = $imageFile->resizeImageHeight($uploadfile, 300);
                        $referenceDocument->setLink($filename);
                        $referenceDocumentRepository->save($referenceDocument);
                        $body .= "Faýl ýüklendi: " . $filename . "\n";
                        //$this->systemEventManager->create(null, SystemEventTypeEnum::EVENT_UPDATE, EntityTypeEnum::ENTITY_USER,
                        //        $this->getUser()->getId(), EntityTypeEnum::ENTITY_ENROLLEDSTUDENT, 0, "Upload file: " . $enrolledStudent->getSystemId());
                    } else {
                        $body .= "Faýl ýüklemekde ýalňyş çykdy!\n";
                    }
                } else {
                    $body .= "Öňem faýl bar. Ilki şony aýryň!\n";
                }
            } catch (\Exception $e) {
                $body = $e->getMessage();
            }
//            $body .= 'okay ';
        }
        return new Response($body);
    }

    /**
     * @Route("/reference_document/listfile", name="reference_document_listfile")
     */
    public function listFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $body = '';
        $id = $request->request->get('id', 0);
        $referenceDocumentRepository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $referenceDocument = $referenceDocumentRepository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($referenceDocument) {
            $filename = $referenceDocument->getLink();
            $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/referencedocuments/" . $filename;
            if (file_exists($filePath)) {
                $body .= "<a href='/referencedocuments/" . $enrolledStudent->getGroupCode() . "/" . $filename . "'>" . $filename . "</a><br><br>";
            } else {
                $body .= "Faýl ýok.<br>";
            }
        }
        return new Response($body);
    }

    /**
     * @Route("/reference_document/deletefile", name="reference_document_deletefile")
     */
    public function deleteFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $body = '';
        $id = $request->request->get('id', 0);
        $referenceDocumentRepository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $referenceDocument = $referenceDocumentRepository->find($id);
        //$body .= "Item call number:" . $item_callnumber;
        if ($referenceDocument) {
            $filename = $referenceDocument->getLink();
            if (strlen($filename) > 0) {
                $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/referencedocuments/" . $filename;
                try {
                    if (file_exists($filePath)) {
                        if (unlink($filePath)) {
                            $referenceDocument->setLink('');
                            $referenceDocumentRepository->save($referenceDocument);
                            $body .= "Faýl pozuldy: " . $filename . "<br>";
                        } else {
                            $body .= "Faýl pozulmady: " . $filename . "<br>";
                        }
                    } else {
                        $body .= "Faýl tapylmady: " . $filename . "<br>";
                        $referenceDocument->setLink('');
                        $referenceDocumentRepository->save($referenceDocument);
                        $body .= "Faýlyň ady aýryldy<br>";
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
     * @Route("/reference_document/downloadfile/{id}", name="reference_document_downloadfile")
     */
    public function downloadFile(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_TEACHER");
        $body = '';
        $id = $request->attributes->get('id', 0);
        $referenceDocumentRepository = $this->getDoctrine()->getRepository(ReferenceDocument::class);
        $referenceDocument = $referenceDocumentRepository->find($id);
        if ($referenceDocument) {
            $filename = $referenceDocument->getLink();
            if (strlen($filename) > 0) {
                $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/referencedocuments/" . $filename;
                try {
                    if (file_exists($filePath)) {
                        $response = new BinaryFileResponse($filePath);
                        $response->setContentDisposition(
//                                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                                ResponseHeaderBag::DISPOSITION_INLINE,
                                $filename
                        );
                        return $response;
                    } else {
                        $body .= "Faýl tapylmady: " . $filename . "<br>";
                        $body .= $filePath;
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

}
