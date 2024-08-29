<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Tests\Controller;

use App\Controller\DataEditorController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of DataEditorControllerTest
 *
 * @author NMammedov
 */
class DataEditorControllerTest extends WebTestCase {

    //put your code here
    public function testUpdate() {
        $client = static::createClient();
//        $crawler = $client->request('GET', '/custom/tableeditor/update');
        $client->xmlHttpRequest('POST', '/custom/tableeditor/update',
                [
                    'table' => 'enrolled_student',
                    'fields' => 'lastname_turkmen,firstname_turkmen,region',
                    'id' => [0, 0, 0],
                    'lastname_turkmen' => ['a', 'b', 'c'],
        ]);
//        print $client->getResponse();
    }

}
