<?php

namespace CertificatTest\Controller;

/**
 * Class IndexControllerTest
 * @package Certificat\Test\Controller
 */
class IndexControllerTest extends BaseControllerTest
{

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('ce/index');
        $this->assertQuery('form#login'); // id of login form
        $this->assertQuery('form#register'); // id of register participant form
        $this->assertQuery('form#register-organization'); // id of register organization form
    }

}
