<?php

use App\Application;
use Silex\WebTestCase;

class ApplicationTest extends WebTestCase
{
    public function createApplication()
    {
        // Silex
        $app = new Application('test');
        $app['session.test'] = true;

        return $app;
    }

    public function test404()
    {
        $client = $this->createClient();

        $client->request('GET', '/give-me-a-404');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testFullForm()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/form');
        $this->assertEquals('France', $crawler->filter('form select[id=form_country] option[value=FR]')->text());

        $form = $crawler->selectButton('Submit')->form();
        $crawler = $client->submit($form);
        $this->assertEquals(1, $crawler->filter('.alert-danger')->count());
    }
}

