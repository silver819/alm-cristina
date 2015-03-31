<?php

namespace Reservable\ActivityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testRegisterLodgingOK()
    {
        fwrite(STDOUT, "*** TEST Register lodging OK ***\n");
        // Login
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');
        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']  = 'cristina';
        $form['_password']  = 'cristina';
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
        fwrite(STDOUT, "\t- User logged\n");

        // Ir a la página de actividades y registrar una propiedad
        $crawler            = $client->request('GET', '/es/new-property');
        fwrite(STDOUT, "\t- Clicked on the link to register the lodging\n");

        $form               = $crawler->selectButton('_registerActivity')->form();
        $form['activity[name]']        = 'Automatic lodging';
        $form['activity[price]']       = '50';
        $form['activity[description]'] = 'Propiedad dada de alta automaticamente';
        $form['activity[address]']     = 'direccion automatic lodging';
        fwrite(STDOUT, "\t- Form completed\n");
        $crawler            = $client->submit($form);

        fwrite(STDOUT, "\t- Form sended\n");
        $this->assertTrue($crawler->filter('html:contains("¡La propiedad ha sido registrada con exito!")')->count() > 0);

        // tearDown
    }

    public function testModifyLodgings()
    {
        fwrite(STDOUT, "*** TEST Modify lodging ***\n");
        // Login
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');
        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']  = 'cristina';
        $form['_password']  = 'cristina';
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
        fwrite(STDOUT, "\t- User logged\n");

        // Ir a la página de actividades
        $crawler            = $client->request('GET', '/es/view-properties');
        fwrite(STDOUT, "\t- Clicked on the link to view the lodging\n");
        $this->assertTrue($crawler->filter('html:contains("Ver propiedades")')->count() > 0);

        // tearDown
    }
}
