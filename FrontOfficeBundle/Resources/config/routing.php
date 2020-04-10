<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('su_front_office_homepage', new Route('/', array(
    '_controller' => 'SuFrontOfficeBundle:Landing:index',
)));

//How it works
$collection->add('front_office_howitworks', new Route('/how-it-works', array(
    '_controller' => 'SuFrontOfficeBundle:ContentPages:howitworks',
)));

//Privacy Policy
$collection->add('front_office_privacypolicy', new Route('/privacy-policy', array(
    '_controller' => 'SuFrontOfficeBundle:ContentPages:privacyPolicy',
)));

//Terms and Conditions
$collection->add('front_office_terms', new Route('/terms-and-conditions', array(
    '_controller' => 'SuFrontOfficeBundle:ContentPages:terms',
)));

return $collection;
