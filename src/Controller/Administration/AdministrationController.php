<?php

namespace App\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\SystemEventManager;
use App\Service\SystemInfoManager;
use App\Service\LdapServiceManager;
use App\Enum\SystemEventTypeEnum;
use App\Enum\EntityTypeEnum;

class AdministrationController extends AbstractController {

    private $systemEventManager;
    private $systemInfoManager;
    private $ldapServiceManager;

    function __construct(SystemEventManager $systemEventManager, SystemInfoManager $systemInfoManager, LdapServiceManager $ldapServiceManager) {
        $this->systemEventManager = $systemEventManager;
        $this->systemInfoManager = $systemInfoManager;
        $this->ldapServiceManager = $ldapServiceManager;
    }

    /**
     * @Route("/administration/index", name="administration_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('administration/index.html.twig', [
                    'controller_name' => 'AdministrationController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/administration/dumpData", name="administration_dumpData")
     */
    public function dumpData(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $content = '';

        $content = shell_exec("C:\wamp64\bin\mysql\mysql5.7.28\bin\mysqldump.exe -u root --password=WWVeIjKiH7XVuFB1 campus > C:\campus\www\campus3\public\build\dred\campus" . date('Y-m-d') . ".sql");
        $content .= "<br><br>campus" . date('Y-m-d') . ".sql is generated. ";
        $content .= file_exists("C:\campus\www\campus3\public\build\dred\campus" . date('Y-m-d') . ".sql");
        $content .= " <a href='/build/dred/campus" . date('Y-m-d') . ".sql'>Download</a>";

        return $this->render('administration/index.html.twig', [
                    'controller_name' => 'AdministrationController',
                    'content' => $content,
        ]);
    }

//    public function dumpData(Request $request) {
//        $this->denyAccessUnlessGranted("ROLE_ADMIN");
//        $content = '';
//        $filename = "campus" . date('Y-m-d') . ".sql";
//        $backupPath = $this->systemInfoManager->getRootPath() . "/var/backup/" . $filename;
//        $content = shell_exec($_SERVER['MYSQLDUMP_PATH'] . " -u " . $_SERVER['DATABASE_USER'] . " --password = " . $_SERVER['DATABASE_PASSWORD'] . " campus > " . $backupPath);
//        $content .= "<br><br>" . $filename . " is generated. ";
//        $content .= file_exists($backupPath);
//        $content .= " <a href='/fileserver/getadmin/backup|" . $filename . "'>Download</a>";
//
//        return $this->render('administration/index.html.twig', [
//                    'controller_name' => 'AdministrationController',
//                    'content' => $content,
//        ]);
//    }

    /**
     * @Route("/administration/clearcache", name="administration_clearcache")
     */
    public function clearCache(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $content = '<h4>Clearing cache</h4>';
        $content .= "<br><br>Cache clear command is run at " . date('Y-m-d H:i:s')."<br><br>";
        $command = "php ".$this->systemInfoManager->getRootPath()."/bin/console cache:clear";
        $content .= shell_exec($command);
        
        return $this->render('administration/index.html.twig', [
                    'controller_name' => 'AdministrationController',
                    'content' => $content,
        ]);
    }
}
