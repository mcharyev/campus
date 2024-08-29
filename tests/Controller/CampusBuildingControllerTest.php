<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// tests/Controller/CampusBuildingControllerTest.php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of CampusBuildingControllerTest
 *
 * @author nazar
 */
class CampusBuildingControllerTest extends WebTestCase {

    public function testIndex() {
        $client = static::createClient();
        $client->request('GET', '/faculty/campusbuilding');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

//    public function testUpdate() {
//        $client = static::createClient();
//        $crawler = $client->request('GET', '/faculty/campusbuilding');
//        $client->xmlHttpRequest('POST', '/faculty/campusbuilding/update',
//                [
//                    'id' => 1,
//                    'system_id' => '1',
//                    'letter_code' => 'E',
//                    'name_english' => 'MainBuilding',
//                    'name_turkmen' => 'Esasy Bina',
//                    'campus_location_id' => 1
//        ]);
//        print $client->getResponse();
//         $campusBuilding->setSystemId($request->request->get('system_id'))
//                ->setLetterCode($request->request->get('letter_code'))
//                ->setNameEnglish($request->request->get('name_english'))
//                ->setNameTurkmen($request->request->get('name_turkmen'))
//                ->setCampusLocation($request->request->get('campus_location_id'));
//    }

}
