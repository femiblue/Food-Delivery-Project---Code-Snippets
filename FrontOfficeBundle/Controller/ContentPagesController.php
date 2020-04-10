<?php

namespace Su\FrontOfficeBundle\Controller;

use Su\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Su\RestaurantBundle\Entity\category;
use Su\RestaurantBundle\Entity\Specialoffers;
use Su\RestaurantBundle\Entity\Dish;
use Su\RestaurantBundle\Entity\Shop;
use Su\RestaurantBundle\Entity\Favorites;

class ContentPagesController extends Controller
{
    public function indexAction()
    {    
         $landingObj      = $this->container->get('landing');
         $results_cat     = $landingObj->getTopMenuAction();//print_r($results_cat);
         $results_cate    = $landingObj->getCategoriesAction(); //print_r($results_cate);
         
         $shopObj         = $this->container->get('shop');
         $results_fav     = $shopObj->subFavoritesAction();//print_r($results_fav);die();
         
         $cartObj          = $this->container->get('cart');
         $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
         
         
         return $this->render('SuFrontOfficeBundle:ContentPages:index.html.twig', 
         
         
         
         array(
         'entity_cat'      => $results_cat,
         'entity_fav'      => $results_fav,
         'entity_cate'     => $results_cate,
         'num_cart_items'  => $num_cart_items,
         'page_title'      => "Welcome",
  
        ));
    }
    
    //How it works
    /**
     * Renders the content of the shopping basket
     *
     * @Route("/how-it-works", name="front_office_howitworks")
     */
    public function howitworksAction(){
         
         $landingObj      = $this->container->get('landing');
         $results_cat     = $landingObj->getTopMenuAction();//print_r($results_cat);
         $results_cate    = $landingObj->getCategoriesAction(); //print_r($results_cate);
         
         $shopObj         = $this->container->get('shop');
         $results_fav     = $shopObj->subFavoritesAction();//print_r($results_fav);die();
         
         $cartObj          = $this->container->get('cart');
         $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
         
         
         
         
         
         
         return $this->render('SuFrontOfficeBundle:ContentPages:howitworks.html.twig', 
         array(
         'entity_cat'      => $results_cat,
         'entity_fav'      => $results_fav,
         'entity_cate'     => $results_cate,
         'num_cart_items'  => $num_cart_items,
         'page_title'      => "How It Works",
  
        ));
    }  
    
    
    //Privacy Policy
    /**
     * Renders the content of the shopping basket
     *
     * @Route("/privacy-policy", name="front_office_privacypolicy")
     */
    public function privacyPolicyAction(){
         
         $landingObj      = $this->container->get('landing');
         $results_cat     = $landingObj->getTopMenuAction();//print_r($results_cat);
         $results_cate    = $landingObj->getCategoriesAction(); //print_r($results_cate);
         
         $shopObj         = $this->container->get('shop');
         $results_fav     = $shopObj->subFavoritesAction();//print_r($results_fav);die();
         
         $cartObj          = $this->container->get('cart');
         $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
         
         
         
         
         
         
         return $this->render('SuFrontOfficeBundle:ContentPages:privacy.html.twig', 
         array(
         'entity_cat'      => $results_cat,
         'entity_fav'      => $results_fav,
         'entity_cate'     => $results_cate,
         'num_cart_items'  => $num_cart_items,
         'page_title'      => "Privacy Policy",
  
        ));
    }  
    
    
    //Terms and Condition
    /**
     * Renders the content of the shopping basket
     *
     * @Route("/terms-and-conditions", name="front_office_terms")
     */
    public function termsAction(){
         
         $landingObj      = $this->container->get('landing');
         $results_cat     = $landingObj->getTopMenuAction();//print_r($results_cat);
         $results_cate    = $landingObj->getCategoriesAction(); //print_r($results_cate);
         
         $shopObj         = $this->container->get('shop');
         $results_fav     = $shopObj->subFavoritesAction();//print_r($results_fav);die();
         
         $cartObj          = $this->container->get('cart');
         $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
         
         
         
         
         
         
         return $this->render('SuFrontOfficeBundle:ContentPages:terms.html.twig', 
         array(
         'entity_cat'      => $results_cat,
         'entity_fav'      => $results_fav,
         'entity_cate'     => $results_cate,
         'num_cart_items'  => $num_cart_items,
         'page_title'      => "Terms and Conditions",
  
        ));
    }  
    
  
     
}
