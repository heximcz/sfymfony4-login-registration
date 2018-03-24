<?php

namespace App\Tests\Controller\User;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{

    public function testUserLoginPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/login');

        // check response code value
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // asserts that the response matches the h1 selector
        $this->assertGreaterThan(0, $crawler->filter('h1')->count());

        // check login form
        $this->assertGreaterThan(
            0,
            $crawler->filter('form.form-signin')->count(),
            'This page contain form with form-signin class.'
        );

        // check login header text
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("Sign in")')->count(),
            'Page contain login header text "Sign in".'
        );

        // check content type
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'text/html; charset=UTF-8'
            ),
            'The "Content-Type" header is "text/html; charset=UTF-8"'
        );

        // cache must revalidate
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'cache-control',
                'max-age=0, must-revalidate, private'
            ),
            'The "Cache-Control" header must be "max-age=0, must-revalidate, private"'
        );
    }

    public function testUserLoginPageSendFormUnconfirmedUser()
    {
        $client = static::createClient();

        // load fixtures
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $fixture = new UserFixtures($container->get('security.password_encoder'));
        $fixture->load($entityManager);

        $crawler = $client->request('GET', '/en/login');

        $form = $crawler->selectButton('Login')->form();
        $form['form_login[_username]'] = 'test@domain.com';
        $form['form_login[_password]'] = 'Pa33w0rd';
        $client->submit($form);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
            if ($client->getResponse()->isRedirect()) {
                $client->followRedirect();
            }
        }
        // check response code value
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // check url
        $this->assertRegExp(
            '(\/en\/account-not-confirmed\/)',
            $client->getRequest()->getUri(),
            'URL contain "/en/account-not-confirmed/"'
        );

    }

    public function testUserLoginPageSendFormConfirmedUser()
    {
        $client = static::createClient();

        // load fixtures
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $fixture = new UserFixtures($container->get('security.password_encoder'));
        $fixture->load($entityManager);

        $crawler = $client->request('GET', '/en/login');

        $form = $crawler->selectButton('Login')->form();
        $form['form_login[_username]'] = 'test2@domain.com';
        $form['form_login[_password]'] = 'Pa33w0rd';
        $client->submit($form);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
            if ($client->getResponse()->isRedirect()) {
                $client->followRedirect();
            }
        }
        // check response code value
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // check url
        $this->assertRegExp(
            '(\/en\/app)',
            $client->getRequest()->getUri(),
            'Backend URL contain "/en/app".'
        );
        // check user name
        $crawler = $client->getCrawler();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("John Doo")')->count(),
            'User "John Doo" logged in.'
        );
    }

    public function testUserRegistrationPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/register');

        // check response code value
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // asserts that the response matches the h1 selector
        $this->assertGreaterThan(0, $crawler->filter('h1')->count());

        // check login form
        $this->assertGreaterThan(
            0,
            $crawler->filter('form.form-register')->count(),
            'This page contain form with "form-register" class.'
        );

        // check login header text
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("Register")')->count(),
            'Page contain login header text "Register"'
        );

        // check content type
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'text/html; charset=UTF-8'
            ),
            'The "Content-Type" header is "text/html; charset=UTF-8"'
        );

        // cache must revalidate
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'cache-control',
                'max-age=0, must-revalidate, private'
            ),
            'The "Cache-Control" header must be "max-age=0, must-revalidate, private"'
        );
    }

    public function testUserRegisterPageSendForm()
    {
        $client = static::createClient();

        // load fixtures
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $fixture = new UserFixtures($container->get('security.password_encoder'));
        $fixture->load($entityManager);

        $crawler = $client->request('GET', '/en/register');

        $form = $crawler->selectButton('Register')->form();
        $form['app_bundle_user_registration_form[userName]'] = 'test123';
        $form['app_bundle_user_registration_form[email]'] = 'test@gmail.com';
        $form['app_bundle_user_registration_form[plainPassword][first]'] = 'Pa33w0rd';
        $form['app_bundle_user_registration_form[plainPassword][second]'] = 'Pa33w0rd';
        $client->submit($form);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }
        // check response code value
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // check info alert
        $crawler = $client->getCrawler();
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Registration is complete")')->count());

    }

    //        dump($client->getResponse()->getContent()); die;

}