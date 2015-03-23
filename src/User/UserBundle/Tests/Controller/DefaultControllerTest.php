<?php

namespace User\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    // Logueo correcto
    public function testLoginOK()
    {
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');

        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']   = 'cristina';
        $form['_password']   = 'cristina';

        $crawler            = $client->submit($form);

        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
    }

    // Logueo incorrecto
    public function testLoginWRONG()
    {
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');

        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']   = 'cristina';
        $form['_password']   = 'wrongPassword';

        $crawler            = $client->submit($form);

        // tearDown

        $this->assertTrue($crawler->filter('html:contains("Nombre de usuario:")')->count() > 0);
    }

    // Registrar usuario
    public function testNewUser()
    {
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/register');

        $form               = $crawler->selectButton('Registrar')->form();
        $form['fos_user_registration_form[email]']                 = 'cristinasanghezgarcia2@gmail.com';
        $form['fos_user_registration_form[username]']              = 'CristinaTest';
        $form['fos_user_registration_form[plainPassword][first]']  = 'testintUserBundle';
        $form['fos_user_registration_form[plainPassword][second]'] = 'testintUserBundle';
        $form['fos_user_registration_form[name]']                  = 'Cristina Test';

        $crawler            = $client->submit($form);

        $this->assertTrue($crawler->filter('html:contains("Solo queda un paso...")')->count() > 0);
    }

    // Modificar usuario
    public function testModifyUser()
    {
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/login');
        $form               = $crawler->selectButton('_submit')->form();
        $form['_username']  = 'cristina';
        $form['_password']  = 'cristina';
        $crawler            = $client->submit($form);

        $crawler            = $client->request('GET', '/es/profile/edit');
        $form               = $crawler->selectButton('Actualizar usuario')->form();
        $form['fos_user_profile_form[username]']         = 'Cristina Changed';
        $form['fos_user_profile_form[current_password]'] = 'cristina';
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Cristina Changed")')->count() > 0);

        $crawler            = $client->request('GET', '/es/profile/edit');
        $form               = $crawler->selectButton('Actualizar usuario')->form();
        $form['fos_user_profile_form[username]']         = 'Cristina';
        $form['fos_user_profile_form[current_password]'] = 'cristina';
        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Cristina")')->count() > 0);
    }
}
