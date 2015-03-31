<?php

namespace Bookings\BookingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testAnonymousSearch()
    {
        fwrite(STDOUT, "*** TEST Anonymous search ***\n");
        fwrite(STDOUT, "\t- Search for tennis courts\n");
        $client             = static::createClient();
        $client->followRedirects(true);
        $crawler            = $client->request('GET', '/es/');
        $form               = $crawler->selectButton('_bookingSearch')->form();
        $form['name']       = 'tenis';
        $crawler            = $client->submit($form);

        $this->assertFalse($crawler->filter('html:contains("No hemos encontrado")')->count() > 0);
        fwrite(STDOUT, "\t- Correct search\n");

        // tearDown
    }

    public function testCalendar()
    {
        fwrite(STDOUT, "*** TEST Calendar ***\n");
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

        $crawler            = $client->request('GET', '/es/calendar/');
        fwrite(STDOUT, "\t- Clicked on the calendar link\n");

        $this->assertTrue($crawler->filter('html:contains("Calendario")')->count() > 0);
        fwrite(STDOUT, "\t- Correct calendar\n");

        // tearDown
    }

    public function testConsultBookings()
    {
        fwrite(STDOUT, "*** TEST Consult bookings ***\n");
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

        $crawler            = $client->request('GET', '/es/consult-bookings/');
        fwrite(STDOUT, "\t- Clicked on the consult bookings link\n");

        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
        fwrite(STDOUT, "\t- Correct calendar\n");

        // tearDown
    }

    public function testConsultHistory()
    {
        fwrite(STDOUT, "*** TEST Consult bookings ***\n");
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

        $crawler            = $client->request('GET', '/es/history-bookings/');
        fwrite(STDOUT, "\t- Clicked on the history bookings link\n");

        $this->assertTrue($crawler->filter('html:contains("Historial de reservas")')->count() > 0);
        fwrite(STDOUT, "\t- Correct calendar\n");

        // tearDown
    }
}
