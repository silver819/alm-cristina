<?php

namespace Bookings\BookingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /*public function testCalendar()
    {
        fwrite(STDOUT, "*** TEST Calendar ***\n");
        // Login
        $client = static::createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/es/login');
        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = 'cristina';
        $form['_password'] = 'cristina';
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Gestionar reservas")')->count() > 0);
        fwrite(STDOUT, "\t- User logged\n");

        $crawler = $client->request('GET', '/es/calendar/');
        fwrite(STDOUT, "\t- Clicked on the calendar link\n");

        $this->assertTrue($crawler->filter('html:contains("Calendario")')->count() > 0);
        fwrite(STDOUT, "\t- Correct calendar\n");
        usleep(500000);
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
        usleep(500000);
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
        usleep(500000);
        // tearDown
    }*/

    public function testMakeBooking()
    {
        fwrite(STDOUT, "*** TEST Make booking ***\n");
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

        $crawler            = $client->request('GET', '/es/');
        fwrite(STDOUT, "\t- Clicked on the main page\n");
        $this->assertTrue($crawler->filter('html:contains("Te recomendamos")')->count() > 0);
        fwrite(STDOUT, "\t- Fill search parameters\n");

        $form               = $crawler->selectButton('_bookingSearch')->form();
        $startDate          = date('d/m/Y', strtotime('+2 days'));
        $endDate            = date('d/m/Y', strtotime('+5 days'));
        fwrite(STDOUT, "\t- - - Selected hour mode\n");
        fwrite(STDOUT, "\t- - - Selected date " . $startDate . "\n");
        $form['type']       = "hour";
        $form['date']       = $startDate;
        $form['StartDate']  = $startDate;
        $form['EndDate']    = $endDate;

        $crawler            = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Reservar")')->count() > 0);

        $search = $crawler->selectButton('_sendSelected')->form();
        $crawler            = $client->submit($search);
        fwrite(STDOUT, "\t- Click on the booking button\n");

        $this->assertTrue($crawler->filter('html:contains("Confirmar")')->count() > 0);
        $confirm = $crawler->selectButton('_confirmReserve')->form();
        $crawler            = $client->submit($confirm);
        $this->assertTrue($crawler->filter('html:contains("Reserva gestionada")')->count() > 0);
        fwrite(STDOUT, "\t- Booking done\n");
    }

    public function testCancelReserve(){
        fwrite(STDOUT, "*** TEST Cancel booking ***\n");
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

        $info = $crawler->selectButton('_cancelReserve')->first()->extract(array('reservationid'));
        if(!empty($info)) {
            $_POST['reserveID'] = (int)$info[0];

            $crawler = $client->request('GET', '/es/delete_reserve');
            fwrite(STDOUT, "\t- Reserve cancelled\n");
        }
    }
}
