<?php

namespace App\Controller\IT;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Service\LdapServiceManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class ITController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;
    private $ldapServiceManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, LdapServiceManager $ldapServiceManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->ldapServiceManager = $ldapServiceManager;
    }

    /**
     * @Route("/it/index", name="it_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        return $this->render('it/index.html.twig', [
                    'controller_name' => 'ITController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/it/showcomputerinfo/{computerName?0}", name="it_showcomputerinfo")
     */
    public function showComputer(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_SPECIALIST");
        $computerName = $request->attributes->get('computerName');

        $content = '';
        $content .= $this->ldapServiceManager->getComputerInfo($computerName);

        return $this->render('it/index.html.twig', [
                    'controller_name' => 'ITController',
                    'content' => $content,
        ]);
    }

}
