<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Service\SystemInfoManager;
use App\Service\SystemEventManager;

class FileServer extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
    }

    /**
     * @Route("/fileserver/index", name="fileserver_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('system/index.html.twig', [
                    'controller_name' => 'SystemController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/fileserver/get/{path}", name="fileserver_get")
     */
    public function serveFile(Request $request, $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT) {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $body = '';
        $path = str_replace("|", "\\", $request->attributes->get('path', ''));
        try {
            $filePath = $this->systemInfoManager->getRootPath() . "/var/uploads/" . $path;
            if (file_exists($filePath)) {
                $filename = basename($filePath);
                $response = new BinaryFileResponse($filePath);
                $response->setCache([
                    'public' => true,
                    'private' => false,
                    'max_age' => 31536000,
                    's_maxage' => 31536000
                ]);
                $response->setContentDisposition(
                        $disposition,
                        //ResponseHeaderBag::DISPOSITION_INLINE,
                        $filename
                );
                return $response;
            } else {
                $body .= "Faýl tapylmady: " . $filename . "<br>";
            }
        } catch (\Exception $e) {
            $body = $e->getMessage() . " File:" . $e->getFile() . " Line:" . $e->getLine();
        }
        return new Response($body);
    }
    
    /**
     * @Route("/fileserver/getadmin/{path}", name="fileserver_getadmin")
     */
    public function serveAdminFile(Request $request, $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $body = '';
        $path = str_replace("|", "\\", $request->attributes->get('path', ''));
        try {
            $filePath = $this->systemInfoManager->getRootPath() . "/var/" . $path;
            if (file_exists($filePath)) {
                $filename = basename($filePath);
                $response = new BinaryFileResponse($filePath);
                $response->setCache([
                    'public' => true,
                    'private' => false,
                    'max_age' => 31536000,
                    's_maxage' => 31536000
                ]);
                $response->setContentDisposition(
                        $disposition,
                        //ResponseHeaderBag::DISPOSITION_INLINE,
                        $filename
                );
                return $response;
            } else {
                $body .= "Faýl tapylmady: " . $filePath . "<br>";
            }
        } catch (\Exception $e) {
            $body = $e->getMessage() . " File:" . $e->getFile() . " Line:" . $e->getLine();
        }
        return new Response($body);
    }

}
