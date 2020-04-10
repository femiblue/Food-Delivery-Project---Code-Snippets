<?php

/**
 * @author Femi Bello
 * @copyright 2017
 */


namespace Su\FrontOfficeUserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Su\RestaurantBundle\Entity\Dish;
use Su\RestaurantBundle\Entity\Shop;
use Su\RestaurantBundle\Entity\SiteConfig;
use Su\RestaurantBundle\Entity\Favorites;

/**
 * Controller responsible for the shopping cart
 */
class ShoppingCartController extends Controller
{
    /**
     * Renders the content of the shopping basket
     *
     * @Route("/shopping-cart", name="front_office_user_basket")
     */
    public function indexAction()
    {   
        $cart_summ    = array();
        $session      = new Session();
        $itemCart     = array();
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        unset($itemCart['']); //remove redundant records
        //print_r($itemCart);
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $cartObj          = $this->container->get('cart');
        $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
        //echo "ITEMS IN CART...".$num_cart_items;//die();
        
        if(!empty($itemCart)){ 
            $cart_summ['sub_total']     = 0;
            $cart_summ['tax']           = 0;
            $cart_summ['shipping_cost'] = 0;
            $cartid                     ="";
            $count                      =0;
            $food_store                 ="";

            foreach($itemCart as $itemval){ //Summarize cart values
               $cartid = $itemval['cartid'];
               
               if( ($cartid>=0) )
               { 
                 $cart_summ['sub_total']      += ($itemval['dish_price'] * $itemval['qty']);
                 $cart_summ['tax']            += $itemval['tax'];
                 if($itemval['shop'] != $food_store)
                 {
                   $cart_summ['shipping_cost']   += $itemval['delivery']; //for delivery is calculated per shop
                   $food_store   = $itemval['shop'];
                 }
               }
               $count++;
            }
            $cart_summ['total']  =  $cart_summ['sub_total'] + $cart_summ['tax'] + $cart_summ['shipping_cost']; //get total
        }//print_r($cart_summ);
        return $this->render('SuFrontOfficeUserBundle:ShoppingCart:cart.html.twig', 
         array(
        'entity_cat'         => $results_cat,
        'entity_cart'        => $itemCart,
        'entity_summ'        => $cart_summ,
        'num_cart_items'     => $num_cart_items,
        'page_title'         => "Shopping Cart",
       ));  
    }

    /**
     * Adds a dish to the shopping cart
     * 
     * @Route("/add-to-cart", name="front_office_user_addtocart")
     * @Method("POST")
     * @Template("SuFrontOfficeUserBundle:Shop:cart.html.twig")
     */     
    public function addToBasketAction(Request $request)
    {  
        //initialise variables
        $dish_id               = "";
        $dish_qty              = 0;
        $entity_dish           = array();
        $entity_shop           = array();
        $dd_shop               = "";
        $entity_siteconfig_res = array();
        $entity_siteconfig     = array();
        $tax_fee               = 0;
        $total_fee             = 0;
        $tax_rate              = 0;//2;
        $delivery_fee          = 0;//10.00;
        $sessionVal            = array();
        
        //define ("TAX", "TAX");
        //define ("DELIVERY", "DEL_FEE");

       if (in_array($request->getMethod(), ['POST'], true))
       { 
        $dish_id             = $request->get('dish_id'); //dish_id
        $dish_qty            = $request->get('qty'); //qty
        $dish_discount_price = $request->get('discount_price'); //Set Discounted Price
        $dish_price          = $dish_discount_price;
        //echo "DISH =$dish_id<br/>";
        //echo "QTY =$dish_qty<br/>"; die();
        $landingObj  = $this->container->get('landing');
        $results_cat = $landingObj->getTopMenuAction();//print_r($results_cat);die();
        
        $em          = $this->getDoctrine()->getManager();
        $entity_dish = $em->getRepository('SuRestaurantBundle:Dish')->find($dish_id);
        //print_r($entity_dish);
	    $entity_shop = $em->getRepository('SuRestaurantBundle:Shop')->find($entity_dish->getShop());
        //print_r($entity_shop); //die();
        
        if ((!$entity_dish) || (!$entity_shop)) {
            throw $this->createNotFoundException('Unable to find Dish or Shop entity.');
        }else{
            //pick tax,delivery fee and calculate total 
            $dd_shop                = $entity_dish->getShop();
            //echo "SHOP ID...$dd_shop<br/>";
            $entity_siteconfig = $em->createQuery("SELECT sc.id, sc.taxrate, sc.deliveryfee, sc.otherval
                                          FROM SuRestaurantBundle:SiteConfig sc 
                                          WHERE sc.shop = '$dd_shop' "); 
                                          //print_r($entity_billing_address);
            $results_siteconfig= $entity_siteconfig->getResult();//print_r($results_siteconfig);
            //$entity_siteconfig = $em->getRepository('SuRestaurantBundle:SiteConfig')->find($dd_shop);
            //echo "Site Config";print_r($results_siteconfig); die();
            if(!empty($results_siteconfig))
            {
                $tax_rate          = $results_siteconfig[0]['taxrate'];
                $delivery_fee      = $results_siteconfig[0]['deliveryfee'];
                //print_r($entity_siteconfig); die();
                
                $tax_fee   = ($tax_rate * $entity_dish->getPrice())/100; //Tax amount
                $total_fee = ($entity_dish->getPrice() * $dish_qty) + $tax_fee + $delivery_fee; // Get Total Fee
            }
            $dish_name = $entity_dish->getName();
            
            //if there is no discount use dish price
            if($dish_discount_price == 0)
            {
              $dish_price = $entity_dish->getPrice();
            }
            //Add to Cart Session
            $sessionVal['dish_id']    = $entity_dish->getDishId();
            $sessionVal['dish_name']  = $entity_dish->getName();
            $sessionVal['dish_image'] = $entity_dish->getImage();
            $sessionVal['dish_price'] = $dish_price;
            $sessionVal['orig_price'] = $entity_dish->getPrice();
            $sessionVal['disc_price'] = $dish_discount_price;
            $sessionVal['qty']        = $dish_qty;
            $sessionVal['tax']        = $tax_fee;
            $sessionVal['delivery']   = $delivery_fee;
            $sessionVal['shop']       = $dd_shop;
            $session = new Session();
            //$session->start(); 
             
            $itemCart = $session->get('itemCart');
            if(!empty($itemCart)){
                end($itemCart);         // move the internal pointer to the end of the array
                $key      = key($itemCart); 
                $sessionVal['cartid']   = ($key+1);
            }else{
                $sessionVal['cartid']   = 0;
            }
            
            if(!empty($itemCart)){
              array_push($itemCart, $sessionVal);
            }else{
              $itemCart[0] = $sessionVal;  
            }
              
            $session->set('itemCart', $itemCart);
              
            //$this->get('session')->set('aBasket', $sessionVal);
          }
        //$session->clear('itemCart');
        //print_r($session);die();
        //print_r($entity_siteconfig); die("This is where it ends");
        $session->getFlashBag()->add('success', "$dish_name was successfully added to cart");
        /*
        return $this->render('SuFrontOfficeUserBundle:Shop:cart.html.twig', 
         array(
        'entity_dish'      => $entity_dish,
        'entity_shop'      => $entity_shop,
        'entity_cat'       => $results_cat,
       )); 
       */
       return $this->redirect($this->generateUrl('front_office_user_basket'));
        
       }else{
        return $this->redirect($this->generateUrl('front_office_user_basket'));
       }
       
    }
    
    /**
     * Clear all Item from the shopping cart
     * 
     * @Route("/clear-cart", name="front_office_user_clearcart")
     * @Method("POST")
     * @Template("SuFrontOfficeUserBundle:Shop:cart.html.twig")
     * 
     */     
    public function clearBasketAction(Request $request)
    {  
       $session      = new Session(); 
       $session->clear('itemCart'); //clear the shopping cary
       
       $landingObj   = $this->container->get('landing');
       $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $results_cate = $landingObj->getCategoriesAction(); //print_r($results_cate);
       $session->getFlashBag()->add('success', "Your Shopping Basket has been emptied");
       return $this->redirect($this->generateUrl('front_office_user_basket'));   
        
    }
    
    /**
     * Remove an item from shopping cart
     * @Route("/rem-from-cart/{id}", name="front_office_user_removeitemfromcart")
     * 
     */
    public function removeItemFromBasketAction($id)
    {   
        $item_counter = 0;
        $temp_counter = 0;
        $session      = new Session();
        $itemCartTemp = array();
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        $itemName     = (isset($itemCart[$id]['dish_name'])) ? $itemCart[$id]['dish_name'] : "Item";
        foreach($itemCart as $itemCart_val){
            if($item_counter == $id){
                unset($itemCart[$item_counter]);
            }else{
                $itemCartTemp[$temp_counter] = $itemCart_val;
                $temp_counter++;
            }
            $item_counter++;
        }
        //sync 
        $session->clear('itemCart'); //clear all items in cart
        $session->set('itemCart', $itemCartTemp); //set updated items to cart
        //Flash message and redirect to Cart
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        
        $session->getFlashBag()->add('success', "$itemName was successfully removed from cart");
        return $this->redirect($this->generateUrl('front_office_user_basket'));
       
                
    }
    /**
     * Update Cart Item when Quantity is changed
     * 
     * @Route("/update-cart-item", name="front_office_user_update_cart_item")
     * @Method("POST")
     * @Template("SuFrontOfficeUserBundle:Shop:cart.html.twig")
     * 
     */     
    public function updateCartItemAction(Request $request)
    {  
       $session      = new Session();
       $itemcount    = 0;
       $itemCart     = $session->get('itemCart'); //Get existing cart items
       
       unset($itemCart['']); //remove redundant records
       //print_r($itemCart); 
       if (in_array($request->getMethod(), ['POST'], true))
       { 
        foreach($itemCart as $itemCart_val)
        {  //use this loop to update a cart item 
            $cartid_key  = 'cartid_'.$itemcount.'';
            $qty_key     = 'quantity_'.$itemcount.'';
            
            $cartid      = $request->get(''."$cartid_key".''); //cart id
            $qty         = $request->get(''."$qty_key".''); //qty
            //set new qty for selected cart item
            if($cartid!="" && ($cartid >= 0))
            {
              $itemCart[$cartid]['qty'] = $qty;              
            }
            $itemcount++;
        }
        //sync 
        $session->clear('itemCart'); //clear all items in cart
        $session->set('itemCart', $itemCart); //set updated items to cart
        
       // print_r($itemCart); die();
        $session->getFlashBag()->add('success', "Your Shopping Basket was updated");
       }

       
       return $this->redirect($this->generateUrl('front_office_user_basket'));   
        
    }
    
    /**
     * Generate Number of Items in the cart
     *
     */     
    public function getNumberOfCartItemsAction()
    {  
       $number_of_cart_items = 0; 
       $session              = new Session();
       //$session->clear('itemCart'); //clear all items in cart
       $itemCart             = $session->get('itemCart'); //Get existing cart items  
       unset($itemCart['']); //remove redundant records     
       if(!empty($itemCart)){
           foreach($itemCart as $itemval){
            $number_of_cart_items += $itemval['qty'];
           }
       }
       return $number_of_cart_items;   
        
    }
    
}
