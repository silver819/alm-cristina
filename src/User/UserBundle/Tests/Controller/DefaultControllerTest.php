<?php

namespace User\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    // Logueo correcto
    public function testLoginOK()
    {
        fwrite(STDOUT, "*** TEST Login OK ***\n");
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');

        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']   = 'cristina';
        $form['_password']   = 'cristina';

        fwrite(STDOUT, "\t- Form completed\n");

        $crawler            = $client->submit($form);

        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
    }

    // Logueo incorrecto
    public function testLoginWRONG()
    {
        fwrite(STDOUT, "*** TEST Login WRONG ***\n");
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');

        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']   = 'cristina';
        $form['_password']   = 'wrongPassword';

        fwrite(STDOUT, "\t- Form completed\n");

        $crawler            = $client->submit($form);
        fwrite(STDOUT, "\t- User not logged\n");

        // tearDown

        $this->assertTrue($crawler->filter('html:contains("Entrar")')->count() > 0);
    }

    /*
    // Registrar usuario
    public function testNewUser()
    {
        fwrite(STDOUT, "*** TEST Register user ***\n");

        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/register');
        fwrite(STDOUT, "\t- Click on the link to register\n");

        $form               = $crawler->selectButton('Registrar')->form();
        $form['fos_user_registration_form[email]']                 = 'cristinasanghezgarcia2@gmail.com';
        $form['fos_user_registration_form[username]']              = 'CristinaTest';
        $form['fos_user_registration_form[plainPassword][first]']  = 'testintUserBundle';
        $form['fos_user_registration_form[plainPassword][second]'] = 'testintUserBundle';
        $form['fos_user_registration_form[name]']                  = 'Cristina Test';
        fwrite(STDOUT, "\t- Data filled\n");

        $crawler            = $client->submit($form);
        fwrite(STDOUT, "\t- Form sended\n");
        fwrite(STDOUT, "\t- User registered\n");
        $this->assertTrue($crawler->filter('html:contains("Solo queda un paso...")')->count() > 0);
    }

    // Modificar usuario
    public function testModifyUser()
    {
        fwrite(STDOUT, "*** TEST Modify User ***\n");
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');
        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']  = 'cristina';
        $form['_password']  = 'cristina';
        $crawler            = $client->submit($form);
        fwrite(STDOUT, "\t- User logged\n");

        $crawler            = $client->request('GET', '/es/profile/edit');
        fwrite(STDOUT, "\t- Click on the link to edit profile\n");
        $form               = $crawler->selectButton('Actualizar usuario')->form();
        $form['fos_user_profile_form[username]']         = 'Cristina Changed';
        $form['fos_user_profile_form[current_password]'] = 'cristina';
        fwrite(STDOUT, "\t- Some data changed\n");
        $crawler            = $client->submit($form);
        fwrite(STDOUT, "\t- Form sended\n");
        $this->assertTrue($crawler->filter('html:contains("Cristina Changed")')->count() > 0);

        $crawler            = $client->request('GET', '/es/profile/edit');
        fwrite(STDOUT, "\t- Click on the link to edit profile\n");
        $form               = $crawler->selectButton('Actualizar usuario')->form();
        $form['fos_user_profile_form[username]']         = 'Cristina';
        $form['fos_user_profile_form[current_password]'] = 'cristina';
        fwrite(STDOUT, "\t- Some data changed\n");
        $crawler            = $client->submit($form);
        fwrite(STDOUT, "\t- Form sended\n");
        $this->assertTrue($crawler->filter('html:contains("Cristina")')->count() > 0);
    }

    // Eliminar usuario
    public function testDeleteUser()
    {
        fwrite(STDOUT, "*** TEST Delete user ***\n");
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');

        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']  = 'admin';
        $form['_password']  = 'admin';

        fwrite(STDOUT, "\t- User admin logged\n");
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);

        $crawler            = $client->request('GET', '/es/view-users');
        fwrite(STDOUT, "\t- Click on the link to admin users\n");

        $link = $crawler->selectLink('Cristina Test')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Click on the user to delete\n");

        $link = $crawler->selectLink('Eliminar')->link();
        $crawler = $client->click($link);
        fwrite(STDOUT, "\t- Clicked on the delete user link\n");
        $this->assertTrue($crawler->filter('html:contains("Ver usuarios")')->count() > 0);
        fwrite(STDOUT, "\t- Correct deletion\n");
    }*/
}
