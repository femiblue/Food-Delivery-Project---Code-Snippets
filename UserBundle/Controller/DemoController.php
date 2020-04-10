<?php
/**
 * Created by PhpStorm.
 * User: phlema
 * Date: 25/09/2016
 * Time: 05:43 PM
 */

namespace Su\UserBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;

class DemoController extends FOSRestController
{
    public function getDemosAction()
    {
        $data = array("hello" => "world");
        $view = $this->view($data);
        return $this->handleView($view);
    }
}