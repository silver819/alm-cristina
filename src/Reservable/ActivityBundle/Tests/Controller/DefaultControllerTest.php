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
        $crawler            = $client->request('GET', '/es/admin/view-properties');
        fwrite(STDOUT, "\t- Clicked on the link to view the lodging\n");

        // Selección de una propiedad
        $link = $crawler->selectLink('Automatic lodging')->link();
        $crawler = $client->click($link);
        $this->assertTrue($crawler->filter('html:contains("Propiedad dada de alta automaticamente")')->count() > 0);

        // Modificar
        $link = $crawler->selectLink('Modificar')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Clicked on the modify link\n");
        $this->assertTrue($crawler->filter('html:contains("Guardar")')->count() > 0);

        $form               = $crawler->selectButton('submit')->form();
        $form['description']= 'Propiedad dada de alta automaticamente y modificada';
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Guardar")')->count() > 0);
        fwrite(STDOUT, "\t- Correct modification\n");

        // Desactivar
        $crawler            = $client->request('GET', '/es/admin/view-properties');
        $link = $crawler->selectLink('Automatic lodging')->link();
        $crawler = $client->click($link);
        $link = $crawler->selectLink('Desactivar')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Clicked on the deactive link\n");
        $this->assertTrue($crawler->filter('html:contains("Activar")')->count() > 0);
        fwrite(STDOUT, "\t- Correct deactivation\n");

        // Activar
        $crawler            = $client->request('GET', '/es/admin/view-properties');
        $link = $crawler->selectLink('Automatic lodging')->link();
        $crawler = $client->click($link);
        $link = $crawler->selectLink('Activar')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Clicked on the active link\n");
        $this->assertTrue($crawler->filter('html:contains("Desactivar")')->count() > 0);
        fwrite(STDOUT, "\t- Correct activarion\n");

        // Eliminar
        $crawler            = $client->request('GET', '/es/admin/view-properties');
        $link = $crawler->selectLink('Automatic lodging')->link();
        $crawler = $client->click($link);
        $link = $crawler->selectLink('Eliminar')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Clicked on the delete link\n");
        $this->assertTrue($crawler->filter('html:contains("Ver propiedades")')->count() > 0);
        fwrite(STDOUT, "\t- Correct deletion\n");

        // tearDown
    }
}
