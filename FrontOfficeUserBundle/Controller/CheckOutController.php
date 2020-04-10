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
use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;
use Swiftmailer\Swiftmailer\Lib\Swift_required;
use Swiftmailer\Swiftmailer\Lib\Classes\Swift\Message;

use Su\RestaurantBundle\Entity\Dish;
use Su\RestaurantBundle\Entity\Shop;
use Su\RestaurantBundle\Entity\SiteConfig;
use Su\RestaurantBundle\Entity\Favorites;
use Su\RestaurantBundle\Entity\OrderDish;
use Su\RestaurantBundle\Entity\ClientOrder;
use Su\RestaurantBundle\Entity\DeliveryOrder;
use Su\RestaurantBundle\Entity\ClientAddress;
use Su\FrontOfficeUserBundle\Form\BillingAddressType;
use Su\FrontOfficeUserBundle\Form\BillingAddresFilterType;
use Su\FrontOfficeUserBundle\Form\EditBillingAddressType;
use Su\FrontOfficeUserBundle\Form\EditBillingAddresFilterType;

/**
 * Controller responsible for Billing Address, Placing Order and Payment
 */
class CheckOutController extends Controller
{
    /**
     * Renders all Addresses that belongs to logged in user and presents form to create a new address
     *
     * @Route("/billing-address", name="front_office_user_address")
     */
    public function indexAction()
    {   
        $entity = new ClientAddress();
        $form   = $this->createForm(new BillingAddressType(), $entity);

        $userId       = ""; 
        $cart_summ    = array();
        $session      = new Session();
        $billing_check= false;
        //$itemCart     = $session->get('itemCart'); //Get existing cart items
        //print_r($itemCart);
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $billingObj   = $this->container->get('billing');      
        //$myOrders     = $billingObj->myOrdersAction(0);   
        //print_r($myOrders);
        $mybillingaddresses   = array();
        $mybillingaddresses   = $billingObj->getMyBillingAddressesAction();
        //Cart Items
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        $billAddress  = $session->get('billAddress'); //Get existing address for non registered users
        $item_counter = 0;
        if(empty($mybillingaddresses) && !empty($billAddress))
        {
            $mybillingaddresses = $billAddress;
            if(!empty($itemCart)){
                foreach($itemCart as $itemval){
                    $itemCart[$item_counter]['billingaddress'] = $billAddress[0]['id'];
                    $item_counter++;
                }
                $session->clear('itemCart'); //clear all items in cart
                $session->set('itemCart', $itemCart); //set updated items to cart
                $itemCart     = $session->get('itemCart');
            }
        }
        //print_r($itemCart);
        //print_r($billAddress);
        if(isset($itemCart[0]['billingaddress']))
        {
            $billing_check = true;
        }
        $cartObj              = $this->container->get('cart');
        $num_cart_items       = $cartObj->getNumberOfCartItemsAction();
        
  
        
        //echo "ITEMS IN CART...".$num_cart_items;//die();
        
        /*//check if user is logged in and include the user id in the request
        $user    = $this->getUser();
        if($user){
         $userId = $user->getId(); 
        }
        */
        if(!empty($itemCart)){
            $cart_summ['sub_total']     = 0;
            $cart_summ['tax']           = 0;
            $cart_summ['shipping_cost'] = 0;
            foreach($itemCart as $itemval){ //Summarize cart values
                $cart_summ['sub_total']      += ($itemval['dish_price'] * $itemval['qty']);
                $cart_summ['tax']            += $itemval['tax'];
                $cart_summ['shipping_cost']   = $itemval['delivery'];
            }
            $cart_summ['total']  =  $cart_summ['sub_total'] + $cart_summ['tax'] + $cart_summ['shipping_cost']; //get total
        }
        //print_r($mybillingaddresses);
        return $this->render('SuFrontOfficeUserBundle:CheckOut:address.html.twig', 
         array(
        'entity_cat'             => $results_cat,
        'entity_cart'            => $itemCart,
        'entity_summ'            => $cart_summ,
        'billing_check'          => $billing_check,
        'num_cart_items'         => $num_cart_items,
        'mybillingaddresses'     => $mybillingaddresses,
        'page_title'             => "Billing Address",
        'entity' => $entity,
        'form'   => $form->createView(),
        //'userId' => $userId,
       ));  
    }
    
    
    
     /**
     * Add Billing Address
     * 
     * @Route("/add-billing-address", name="front_office_user_createaddress")
     * @Method("POST")
     * @Template("SuFrontOfficeUserBundle:Shop:address.html.twig")
     */     
    public function addBillingAddressAction(Request $request)
    {  
       $userId         = ""; 
       $latest_address = "";
       $item_counter   = 0;
       $session        = new Session();
       $entity         = new ClientAddress();
       $billAddress    = array();


       $form           = $this->createForm(new BillingAddressType(), $entity);       
       $form->bind($request);
       
       if ($form->isValid()) {
            //check if user is logged in and include the user id in the request
            $user    = $this->getUser();
            if($user){
              $userId = $user->getId(); 
              $entity->setClientId($userId);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            //Get id of inserted address and make default address for current cart
            $latest_address = $entity->getId();
            
                   
           //set address variables
           if($request)
           {
            $billAddress[0]['clientName']  = $request->get('clientName');
            $billAddress[0]['address1']    = $request->get('address1');
            $billAddress[0]['address2']    = $request->get('address2');
            $billAddress[0]['city']        = $request->get('city');
            $billAddress[0]['zipcode']     = $request->get('zipcode');
            $billAddress[0]['nickname']    = $request->get('nickname');
            $billAddress[0]['email']       = $request->get('email');
            $billAddress[0]['phone']       = $request->get('phone');
            $billAddress[0]['id']          = $latest_address;
            $billAddress[0]['mkdefault']   = "Y";
            
           }
           //try put added address in session to take care of unregistered users
           $session->clear('billAddress'); //clear bill address
           $session->set('billAddress', $billAddress); //set updated bill address
            
            //set address for item in cart
            $itemCart       = $session->get('itemCart'); //Get existing cart items
            if(!empty($itemCart)){
                foreach($itemCart as $itemval){
                    $itemCart[$item_counter]['billingaddress'] = $latest_address;
                    $item_counter++;
                }
                $session->clear('itemCart'); //clear all items in cart
                $session->set('itemCart', $itemCart); //set updated items to cart
            }
            
            $session->getFlashBag()->add('success', "Billing Address was created successfully");
       }

            
       return $this->redirect($this->generateUrl('front_office_user_address'));   
        
    }
    
     /**
     * Set an address as billing for an order
     * 
     * @Route("/set-billing-address", name="front_office_user_setaddress")
     * @Method("POST")
     * @Template("SuFrontOfficeUserBundle:Shop:address.html.twig")
     * 
     */     
    public function setBillingAddressAction(Request $request)
    {  
       $item_counter = 0;  
       $session      = new Session(); 
       $addressid    = $request->get('addressid'); //addressid
       $itemCart     = $session->get('itemCart'); //Get existing cart items
       if((!empty($itemCart))){
            foreach($itemCart as $itemval){
                $itemCart[$item_counter]['billingaddress'] = $addressid;
                $item_counter++;
            }
            $session->clear('itemCart'); //clear all items in cart
            $session->set('itemCart', $itemCart); //set updated items to cart
       }
       $em                     = $this->getDoctrine()->getManager();
       $queryBuilder           = $em->createQueryBuilder();
       $user                   = $this->getUser();
       if($user){
         $userId = $user->getId(); 
         //Reset all other addresses for the user
         $userId                 = "'".$userId."'";
         $mkdefault_n            = "'N'";
         $q                      = $queryBuilder
                                  ->update('SuRestaurantBundle:ClientAddress', 'ca')
                                  ->set('ca.mkdefault', $mkdefault_n)
                                  ->where($queryBuilder->expr()->eq('ca.clientId', $userId))
                                  ->getQuery();
                                 
         $p                      = $q->execute();
        
       }
       
       /////////////////////////////////////////////////////////////////////////////////
       //Set address as default billing
       $addressid              = "'".$addressid."'"; 
       $mkdefault_y            = "'Y'"; 
       $q                      = $queryBuilder
                                 ->update('SuRestaurantBundle:ClientAddress', 'ca')
                                 ->set('ca.mkdefault', $mkdefault_y)
                                 ->where($queryBuilder->expr()->eq('ca.id', $addressid))
                                 ->getQuery();
                                 
       $p                      = $q->execute();
       
       //set the address to default billing address
       $session->getFlashBag()->add('success', "Address was set for your pending order");
       return $this->redirect($this->generateUrl('front_office_user_address'));   
        
    }
    
    /**
     * Edit an existing billing address
     * 
     * @Route("/edit-billing-address", name="front_office_user_editaddress")
     * @Method("GET")
     * @Template("SuFrontOfficeUserBundle:Shop:edit_address.html.twig")
     * 
     */
    public function editBillingAddressAction(Request $request)
    {  
        
        $userId       = ""; 
        $cart_summ    = array();
        $session      = new Session();
        $addressid    = $request->get('addressid'); //addressid
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        //print_r($addressid);
        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die();
        $billingObj           = $this->container->get('billing');      
        $mybillingaddress     = array(); //echo "ADDRESS ID...$addressid<br/>";
        $mybillingaddress     = $billingObj->getMyBillingAddressAction($addressid);
        $cartObj              = $this->container->get('cart');
        $num_cart_items       = $cartObj->getNumberOfCartItemsAction();
        
        $entity = new ClientAddress();
        $form   = $this->createForm(new EditBillingAddressType(), $entity);
        //preset form elements
        $form->get('clientName')->setData($mybillingaddress[0]['clientName']);
        $form->get('address1')->setData($mybillingaddress[0]['address1']);
        $form->get('address2')->setData($mybillingaddress[0]['address2']);
        $form->get('city')->setData($mybillingaddress[0]['city']);
        $form->get('zipcode')->setData($mybillingaddress[0]['zipcode']);
        $form->get('nickname')->setData($mybillingaddress[0]['nickname']);
        $form->get('email')->setData($mybillingaddress[0]['email']);
        $form->get('phone')->setData($mybillingaddress[0]['phone']);
        $form->get('id')->setData($mybillingaddress[0]['id']);
        //return $results_billing_address;  
        
        //print_r($mybillingaddress); //die();
        
        return $this->render('SuFrontOfficeUserBundle:CheckOut:edit_address.html.twig', 
         array(
        'entity_cat'             => $results_cat,
        'entity_cart'            => $itemCart,
        'entity_summ'            => $cart_summ,
        'num_cart_items'         => $num_cart_items,
        'page_title'             => "Edit Billing Address",
        'mybillingaddress'       => $mybillingaddress,
        'entity' => $entity,
        'form'   => $form->createView(),
        //'userId' => $userId,
       ));   
        
    }
    
    
    /**
     * List Billing Addresses for Logged in user
     *
     */     
    public function getMyBillingAddressesAction()
    {  
       $userId       = "";
       $item_counter = 0; 
       
       $entity_billing_address = array();
       $results_billing_address= array();
       
       $user         = $this->getUser();
       if($user){
          $userId = $user->getId(); 
          $session                = new Session();
          $em                     = $this->getDoctrine()->getManager();
          $itemCart               = $session->get('itemCart'); //Get existing cart items
          $entity_billing_address = $em->createQuery('SELECT ca.id, ca.clientName, ca.address1, ca.address2,
                                          ca.city, ca.zipcode, ca.nickname, ca.email, ca.phone, ca.mkdefault FROM SuRestaurantBundle:ClientAddress ca 
                                          WHERE ca.clientId ='.$userId.' ORDER BY ca.id DESC'); 
          $results_billing_address= $entity_billing_address->getResult();//print_r($results_billing_address);
          
          $itemCart       = $session->get('itemCart'); //Get existing cart items
          if((!empty($itemCart)) &&(!empty($results_billing_address))){
                foreach($itemCart as $itemval){
                   foreach($results_billing_address as $results_billing_address_val)
                   { 
                     if($results_billing_address_val['mkdefault'] == "Y")
                     {
                      $itemCart[$item_counter]['billingaddress'] = $results_billing_address_val['id'];
                     }
                   }
                   $item_counter++;
                }
                $session->clear('itemCart'); //clear all items in cart
                $session->set('itemCart', $itemCart); //set updated items to cart
          }
       
          //print_r($results_billing_address); die();
          return $results_billing_address;   
       
        }
        return $results_billing_address; 
    }
    
    
    /**
     * List a specific address for Logged in user
     *
     */     
    public function getMyBillingAddressAction($id)
    {  
       $userId       = "";
       $item_counter = 0; 
       $client_filter= ""; 
       $user         = $this->getUser();
       if($user){
              $userId = $user->getId(); 
              if($userId != "")
              {
                $client_filter = "ca.clientId ='$userId' AND";
              }
       }
       $entity_billing_address = array();
       $results_billing_address= array();
       $session                = new Session();
       $em                     = $this->getDoctrine()->getManager();
       $itemCart               = $session->get('itemCart'); //Get existing cart items
       $entity_billing_address = $em->createQuery("SELECT ca.id, ca.clientName, ca.address1, ca.address2,
                                          ca.city, ca.zipcode, ca.nickname, ca.email, ca.phone FROM SuRestaurantBundle:ClientAddress ca 
                                          WHERE $client_filter ca.id = '$id'  ORDER BY ca.id DESC"); 
                                          //print_r($entity_billing_address);
       $results_billing_address= $entity_billing_address->getResult();//print_r($results_billing_address);
       /*
       $itemCart       = $session->get('itemCart'); //Get existing cart items
       if((!empty($itemCart)) &&(!empty($results_billing_address))){
            foreach($itemCart as $itemval){
                $itemCart[$item_counter]['billingaddress'] = $results_billing_address[0]['id'];
                $item_counter++;
            }
            $session->clear('itemCart'); //clear all items in cart
            $session->set('itemCart', $itemCart); //set updated items to cart
       }
       */
       return $results_billing_address;   
        
    }
    
     /**
     * Update Billing Address by client address id
     * @Route("/update-billing-address", name="front_office_user_updateaddress")
     * @Method("POST")
     *
     */     
    public function updateBillingAddressAction(Request $request)
    {  //print_r($request->get('clientName'));die();
       $userId       = "";
       $item_counter = 0; 
       
       $entity_billing_address = array();
       $results_billing_address= array();
       
       if($request)
       {
        $client_name = $request->get('clientName');
        $client_name = "'".$client_name."'"; //Make sure its passed as string else it throws an error
        $address1    = $request->get('address1');
        $address1    = "'".$address1."'";
        $address2    = $request->get('address2');
        $address2    = "'".$address2."'";
        $city        = $request->get('city');
        $city        = "'".$city."'";
        $zipcode     = $request->get('zipcode');
        $zipcode     = "'".$zipcode."'";
        $nickname    = $request->get('nickname');
        $nickname    = "'".$nickname."'";
        $email       = $request->get('email');
        $email       = "'".$email."'";
        $phone       = $request->get('phone');
        $phone       = "'".$phone."'";
        $id          = $request->get('id');
        $id          = "'".$id."'";
         //echo "CLIENT NAME0000000...$client_name<br/>".$request->get('clientName');die();
       }
       
       $user         = $this->getUser();
       if($user){
          $userId = $user->getId(); 
       }
       
       $session                = new Session();
       $em                     = $this->getDoctrine()->getManager();
       $queryBuilder           = $em->createQueryBuilder();
       
       $q                      = $queryBuilder
                                 ->update('SuRestaurantBundle:ClientAddress', 'ca')
                                 ->set('ca.clientName', $client_name)
                                 ->set('ca.address1', $address1)
                                 ->set('ca.address2', $address2)
                                 ->set('ca.city', $city)
                                 ->set('ca.zipcode', $zipcode)
                                 ->set('ca.nickname', $nickname)
                                 ->set('ca.email', $email)
                                 ->set('ca.phone', $phone)
                                 ->where($queryBuilder->expr()->eq('ca.id', $id))
                                 ->getQuery();
                                 
       $p                      = $q->execute();

       $itemCart               = $session->get('itemCart'); //Get existing cart items
       
       //set address variables
       if($request)
       {
        $billAddress[0]['clientName']  = $request->get('clientName');
        $billAddress[0]['address1']    = $request->get('address1');
        $billAddress[0]['address2']    = $request->get('address2');
        $billAddress[0]['city']        = $request->get('city');
        $billAddress[0]['zipcode']     = $request->get('zipcode');
        $billAddress[0]['nickname']    = $request->get('nickname');
        $billAddress[0]['email']       = $request->get('email');
        $billAddress[0]['phone']       = $request->get('phone');
        $billAddress[0]['id']          = $request->get('id');
        $billAddress[0]['mkdefault']   = "Y";
        
       }
       //try put added address in session to take care of unregistered users
       $session->clear('billAddress'); //clear bill address
       $session->set('billAddress', $billAddress); //set updated bill address
   
      //print_r($results_billing_address); die();
       
        
        $session->getFlashBag()->add('success', "Your billing address $address1 was updated");
        return $this->redirect($this->generateUrl('front_office_user_address'));  
        //return $results_billing_address; 
    }
    
    
    /**
     * Delete Billing Address by client address id
     * @Route("/delete-billing-address", name="front_office_user_deleteaddress")
     * @Method("POST")
     *
     */     
    public function deleteBillingAddressAction(Request $request)
    {  //print_r($request->get('clientName'));die();
       $userId       = "";
       $item_counter = 0; 
       
       $entity_billing_address = array();
       $results_billing_address= array();
       
       if($request)
       {
        $address_id = $request->get('id');
        $address_id = "'".$address_id."'"; //Make sure its passed as string else it throws an error
        
         //echo "ADDRESS ID...$address_id<br/>";die();
       }
       
       $user         = $this->getUser();
       if($user){
          $userId = $user->getId(); 
       }
       
       $session                = new Session();
       $em                     = $this->getDoctrine()->getManager();
       $queryBuilder           = $em->createQueryBuilder();
       
       //first check if the address is tied to a previous order
       $entity_order_dish = $em->createQuery("SELECT od.clientAddress FROM SuRestaurantBundle:OrderDish od 
                                          WHERE od.clientAddress = $address_id  ORDER BY od.id DESC"); 
                                          //print_r($entity_order_dish);
       $results_order_dish= $entity_order_dish->getResult();//print_r($results_order_dish); die();
       
       if(empty($results_order_dish))
       {
           $q                      = $queryBuilder
                                     ->delete('SuRestaurantBundle:ClientAddress', 'ca')
                                     ->where($queryBuilder->expr()->eq('ca.id', $address_id))
                                     ->getQuery();
                                     
           $p                      = $q->execute();
           
    
           $itemCart               = $session->get('itemCart'); //Get existing cart items
        
            
            $session->getFlashBag()->add('success', "Billing address was deleted");
            return $this->redirect($this->generateUrl('front_office_user_address'));  
            //return $results_billing_address; 
       }else
       {
        
           $session->getFlashBag()->add('error', "Billing address could not be deleted because it is associated with a previous Order");
           return $this->redirect($this->generateUrl('front_office_user_address')); 
        
       }
       
    }


    /**
     * Place order here
     *
     * @Route("/place-order", name="front_office_user_placeorder")
     */
    public function placeOrderAction(Request $request)
    {   
        $entity = new ClientAddress();
        $form   = $this->createForm(new BillingAddressType(), $entity);

        $userId       = ""; 
        $cart_summ    = array();
        $session      = new Session(); 
     
        $billing_check= false;

        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $billingObj           = $this->container->get('billing');   
        $mybillingaddresses   = array();
        $mybillingaddress     = array(); //the address to used
        $mybillingaddresses   = $billingObj->getMyBillingAddressesAction();
        if(!empty($mybillingaddresses))
        {
          foreach($mybillingaddresses as $mybillingaddresses_val)
          {
            if($mybillingaddresses_val['mkdefault'] = "Y")
            {
                $mybillingaddress = $mybillingaddresses_val;
            }
          }
          
        }
        //Cart Items
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        //print_r($itemCart);
        if(isset($itemCart[0]['billingaddress']))
        {
            $billing_check = true;
        }
        $cartObj              = $this->container->get('cart');
        $num_cart_items       = $cartObj->getNumberOfCartItemsAction();
        
  
        
        //echo "ITEMS IN CART...".$num_cart_items;//die();
        
        /*//check if user is logged in and include the user id in the request
        $user    = $this->getUser();
        if($user){
         $userId = $user->getId(); 
        }
        */
        if(!empty($itemCart)){
            $cart_summ['sub_total']     = 0;
            $cart_summ['tax']           = 0;
            $cart_summ['shipping_cost'] = 0;
            $food_store                 = "";
            foreach($itemCart as $itemval){ //Summarize cart values
                $cart_summ['sub_total']      += ($itemval['dish_price'] * $itemval['qty']);
                $cart_summ['tax']            += $itemval['tax'];
                if($itemval['shop'] != $food_store)
                {
                   $cart_summ['shipping_cost']   += $itemval['delivery']; //for delivery is calculated per shop
                   $food_store   = $itemval['shop'];
                }
               
            }
            $cart_summ['total']  =  $cart_summ['sub_total'] + $cart_summ['tax'] + $cart_summ['shipping_cost']; //get total
        }
        //print_r($mybillingaddress);
        //print_r($cart_summ);
        //$session->clear('billAddress'); //clear all billing
        $session->set('billAddress', $mybillingaddress); //set billing
        //$session->clear('cartSumm'); //clear all cart summ
        $session->set('cartSumm', $cart_summ); //set cart summ
        
        return $this->render('SuFrontOfficeUserBundle:CheckOut:place_order.html.twig', 
         array(
        'entity_cat'             => $results_cat,
        'entity_cart'            => $itemCart,
        'entity_summ'            => $cart_summ,
        'billing_check'          => $billing_check,
        'num_cart_items'         => $num_cart_items,
        'mybillingaddress'       => $mybillingaddress,
        'page_title'             => "Place Order",
        'entity' => $entity,
        'form'   => $form->createView(),
        //'userId' => $userId,
       ));  
    }
        
    
      /**
     * Confirm order here
     *
     * @Route("/confirm-order", name="front_office_user_confirmorder")
     */
    public function confirmOrderAction()
    {   
        $userId       = ""; 
        $order_no     = "";
        $cart_summ    = array();
        $email_vars   = array();
        $session      = new Session();
        $billing_check= false;
        $user         = $this->getUser();
        if($user)
        {
         $userId = $user->getId(); 
        }
        $em           = $this->getDoctrine()->getManager();

        $landingObj   = $this->container->get('landing');
        $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die()
        $billingObj           = $this->container->get('billing');      
        $mybillingaddresses   = array();
        //$mybillingaddresses   = $billingObj->getMyBillingAddressesAction();
        //Cart Items
        $itemCart     = $session->get('itemCart'); //Get existing cart items
        //print_r($itemCart);
        //other sessions
        $cart_summ          = $session->get('cartSumm'); //Get existing cart summ
        $mybillingaddresses = $session->get('billAddress'); //Get billing addy
        //echo "CLIENT NAME...".$mybillingaddresses['clientName'].'<br/>';
        //print_r($cart_summ);
        //print_r($mybillingaddresses); //die();
        
        /*
        if(isset($itemCart[0]['billingaddress']))
        {
            $billing_check = true;
        }
        $cartObj              = $this->container->get('cart');
        $num_cart_items       = $cartObj->getNumberOfCartItemsAction();
        */
        $current_date         = new \DateTime("now"); //date("Y-m-d H:i:s");
        //gen order number
        $order_no             = $this->generateRandomString();
        //Save order dish
        
        foreach($itemCart as $itemval){ //place order for all cart items
           
           //Save order dish
           $order_dish_id    = "";
           if($itemval['dish_id'] != "" ){ //do only if there is dish
             $order_dish = new OrderDish();
             $order_dish->setDish($itemval['dish_id']);
             $order_dish->setNote($order_no);
             $order_dish->setQuantity($itemval['qty']);
             $order_dish->setPrice($itemval['dish_price']);
             $order_dish->setCreationDate($current_date);
             $order_dish->setUpdateDate($current_date);
             $order_dish->setClientAddress($itemval['billingaddress']);
             
             if($order_dish){
               $em->persist($order_dish);
               $em->flush();
               $order_dish_id = $order_dish->getId(); //get $order_dish_id
               
             }

             //return $this->redirect($request->headers->get('referer'));
           
           }
           
           //Save Client Order
           $client_order_id   = "";
           if($order_dish_id != "" ){ //do only if dish order has been initiated
             $client_order = new ClientOrder();
             $client_order->setOrderDishId($order_dish_id);
             $client_order->setDeliveryFee($itemval['delivery']);
             $client_order->setTax($itemval['tax']);
             $client_order->setTotal( $itemval['delivery'] + $itemval['tax'] + ($itemval['dish_price'] * $itemval['qty']) );
             $client_order->setCreationDate($current_date);

             
             if($client_order){
               $em->persist($client_order);
               $em->flush();
               $client_order_id = $client_order->getId(); //get $client_order_id
               
             }
           
           }
           
           
           //save Delivery order
           
           if($client_order_id != "" ){ //do only if client order has been initiated
             $delivery_order = new DeliveryOrder();
             $delivery_order->setClientOrderId($client_order_id);
             $delivery_order->setAddressId($itemval['billingaddress']);
             $delivery_order->setClientId($userId);
             $delivery_order->setStatus(1); //Pending - 1, Paid - 2, Confirmed - 3, Processing - 4, Ready - 5, In-transit - 6, Delivered - 7, Cancelled - 8
             $delivery_order->setUpdateDate($current_date);

             
             if($delivery_order){
               $em->persist($delivery_order);
               $em->flush();
             }
           
           }
           
           
           
        }
        
        
        //Send a mail to client
        $emailObj                  = $this->container->get('order_email');
        
        $email_vars['total']      = $cart_summ['total'];
        $email_vars['email_to']   = $mybillingaddresses['email'];
        $email_vars['client_name']= $mybillingaddresses['clientName'];
        
        //Send Email to Client
        //$emailObj->confirmOrderEmailAction($email_vars);//print_r($results_cat);die()
           
        //Clear Cart
        $session->clear('billAddress'); //clear all billing
        $session->clear('cartSumm'); //clear all cart summ
        $session->clear('itemCart'); //clear all cart items
           
        //goto my orders with success msg
        $session->getFlashBag()->add('success', "Your order was initiated successfully");

        return $this->redirect($this->generateUrl('front_office_user_orders')); 
        
       /* return $this->render('SuFrontOfficeUserBundle:CheckOut:my_orders.html.twig', 
         array(
        'entity_cat'             => $results_cat,
        
       )); */ 
    }
    
    
     /**
      *List All Pending Orders for a Particular User
      *@Route("/orders", name="front_office_user_orders")
      */     
    public function myOrdersAction()
    {  
       $userId        = "";
       $myOrders      = array();
       $orderNos      = array();
       $noteArr       = array();
       $landingObj    = $this->container->get('landing');
       $results_cat   = $landingObj->getTopMenuAction();
       $billingObj    = $this->container->get('billing');   
       $cartObj       = $this->container->get('cart');
       $num_cart_items= $cartObj->getNumberOfCartItemsAction();   
       
       $user          = $this->getUser();
       if($user)
       {
            $userId     = $user->getId(); 
            $myOrders   = $billingObj->getMyOrdersAction(0);   //0 to mean neither delivered nor cancelled
            //print_r($myOrders['orders']); //die();  
            //echo "<br/><br/>";
            //store order nos for further use
            if(!empty($myOrders['orders']))
            {   $order_nos_cnt  = 0;
                $order_nos_cnt1 = 0;
                //Get all distinct orders
                foreach($myOrders['orders'] as $order_no_val) 
                { 
                  if(!in_array($order_no_val['orderDishNote'],$noteArr)) //only unique order numbers
                  { 
                    $noteArr[] = $order_no_val['orderDishNote']; 
                    $orderNos[$order_nos_cnt]['note']        = $order_no_val['orderDishNote']; 
                    $orderNos[$order_nos_cnt]['total']       = 0; 
                    $orderNos[$order_nos_cnt]['order_date']  = $order_no_val['clientOrderDate']->format("F j, Y - g:i a");
                    //$orderNos[$order_nos_cnt]['time_elapsed']= date("Y-m-d H:i:s") - $order_no_val['clientOrderDate']->format('Y-m-d H:i:s');  
                    $orderNos[$order_nos_cnt]['status']      = $order_no_val['deliveryOrderStatus']; 
                    $order_nos_cnt++;
                  }
                  
                }
                //array_unique($orderNos);
                //print_r($orderNos); 
                //echo "<br/><br/>";
                //calculate total for each order
                foreach($orderNos as $orderNos_val)
                {
                    $orderNoteVal = $orderNos_val['note'];
                    foreach($myOrders['orders'] as $order_no_val)
                    {    
                        if($order_no_val['orderDishNote'] == $orderNoteVal){ 
                           $orderNos[$order_nos_cnt1]['total'] += $order_no_val['total'];
                        }
                    }
                    $order_nos_cnt1++;
                }
                
                
            }
    
            //Take care of undefined arrays
            if(empty($myOrders['orders']))
            {
                $myOrders['orders']    = array();
            }
            if(empty($myOrders['addresses']))
            {
                $myOrders['addresses'] = array();
            }
            //print_r($myOrders); //die();   
            return $this->render('SuFrontOfficeUserBundle:CheckOut:my_orders.html.twig', 
             array(
            'entity_cat'             => $results_cat,
            'entity_myorders'        => $myOrders['orders'],
            'entity_addresses'       => $myOrders['addresses'],
            'entity_order_nos'       => $orderNos,
            'num_cart_items'         => $num_cart_items,
            'page_title'             => "My Orders",
            
            
           ));  
       
       }else
       {   //prompt the user to login
           return $this->redirect($this->generateUrl('fos_user_security_login'));
       }
       

        return $my_orders; 
    }
    
     /**
      *List All Past Orders that has been delivered or cancelled for a Particular User
      *@Route("/orders-history", name="front_office_user_ordershistory")
      */     
    public function ordersHistoryAction()
    {  
       $userId                = "";
       $myOrders              = array();
       $history_summ          = array();
       $landingObj            = $this->container->get('landing');
       $results_cat           = $landingObj->getTopMenuAction();
       $billingObj            = $this->container->get('billing');   
       $cartObj               = $this->container->get('cart');
       $num_cart_items        = $cartObj->getNumberOfCartItemsAction();   
       
       $user                  = $this->getUser();
       if($user)
       {
            $userId     = $user->getId(); 
            $myOrders   = $billingObj->getMyOrdersAction(1);  //1 to mean delivered or cancelled 
            //echo "<br/><br/>";
            //Take care of undefined arrays
            if(empty($myOrders['orders']))
            {
                $myOrders['orders']    = array();
            }
            if(empty($myOrders['addresses']))
            {
                $myOrders['addresses'] = array();
            }
            
            //Compose history summary
            if(!empty($myOrders['orders'])){ //print_r($myOrders['orders']); die();
             $history_summ['sub_total']     = 0;
             $history_summ['tax']           = 0;
             $history_summ['shipping_cost'] = 0;
             foreach($myOrders['orders'] as $itemval){ //Summarize cart values
                $history_summ['sub_total']      += ($itemval['orderDishPrice'] * $itemval['orderDishQuantity']);
                $history_summ['tax']            += $itemval['tax'];
                $history_summ['shipping_cost']   = $itemval['deliveryFee'];
             }
             $history_summ['total']  =  $history_summ['sub_total'] + $history_summ['tax'] + $history_summ['shipping_cost']; //get total
            }
            //print_r($myOrders); //die();   
            return $this->render('SuFrontOfficeUserBundle:CheckOut:orders_history.html.twig', 
             array(
            'entity_cat'             => $results_cat,
            'entity_myorders'        => $myOrders['orders'],
            'entity_addresses'       => $myOrders['addresses'],
            'num_cart_items'         => $num_cart_items,
            'page_title'             => "Orders History",
            'entity_summ'            => $history_summ
            
           ));  
       
       }else
       {   //prompt the user to login
           return $this->redirect($this->generateUrl('fos_user_security_login'));
       }
       

        return $my_orders; 
    }
    
    
     /**
      *Param $order_status //Boolean 0 or 1
     * List All Pending or Treated Orders for a Particular User
     *
     */     
    public function getMyOrdersAction($order_status = 0)
    {  
       $userId                = "";
       $item_counter          = 0; 
       $clientAddressId       = "";
       $deliver_status_filter = "";
       $client_address_filter = "";
       
       $entity_billing_address = array();
       $results_billing_address= array();
       $entity_my_orders       = array();
       $results_my_orders      = array();
       $my_orders              = array();
       
       $user         = $this->getUser();
       if($user){
          $userId = $user->getId(); 
          $session                = new Session();
          $em                     = $this->getDoctrine()->getManager();
          //$itemCart               = $session->get('itemCart'); //Get existing cart items
          $entity_billing_address = $em->createQuery('SELECT ca.id, ca.clientName, ca.address1, ca.address2,
                                          ca.city, ca.zipcode, ca.nickname, ca.email, ca.phone FROM SuRestaurantBundle:ClientAddress ca 
                                          WHERE ca.clientId ='.$userId.' ORDER BY ca.id DESC'); 
          $results_billing_address= $entity_billing_address->getResult();//print_r($results_billing_address);
          
          //Store all order addresses in the return array
          $my_orders['addresses'] = $results_billing_address;
          //Create an IN clause of Client Address
          foreach($results_billing_address as $results_billing_address_val)
          {
            $clientAddressId .= "'".$results_billing_address_val['id']."'".",";
          }
          $clientAddressId = rtrim($clientAddressId,","); //remove the last comma
          //form client address filter
          if($clientAddressId != "")
          {
            $client_address_filter = "do.addressId IN ($clientAddressId)";
          }
          //fetch orders corresponding to the above client addresses
          if($order_status == 0)
          {
            //All orders yet to be delivered or cancelled
            $deliver_status_filter = "AND (do.status != '7' AND do.status != '8')";
          }elseif($order_status == 1)
          {
            //All orders cancelled or delivered
            $deliver_status_filter = "AND (do.status = '7' OR do.status = '8')";
          }
          
          $entity_my_orders = $em->createQuery("SELECT do.id, do.clientOrderId, do.addressId, do.clientId, do.status, do.updateDate 
                                          FROM SuRestaurantBundle:DeliveryOrder do
                                          WHERE $client_address_filter $deliver_status_filter ORDER BY do.id DESC"); 
          $results_my_orders= $entity_my_orders->getResult();//print_r($results_my_orders);
          //print_r($clientAddressId); die();
          
          //Begin to store outputs in $my_orders and get info from other entities 
          //Method used here is a lil crud, to be implemented in a better way later
          if(!empty($results_my_orders))
          {
              $my_orders_count = 0;
              foreach($results_my_orders as $results_my_orders_val)
              {
                //
                $clientOrderId                                      = $results_my_orders_val['clientOrderId'];
                $entity_client_orders = $em->createQuery("SELECT co.id, co.orderDishId, co.deliveryFee, co.tax, co.total, co.creationDate 
                                          FROM SuRestaurantBundle:ClientOrder co
                                          WHERE co.id = '$clientOrderId' ORDER BY co.id DESC"); 
                $results_client_orders= $entity_client_orders->getResult();//print_r($results_client_orders);
                
                $my_orders['orders'][$my_orders_count]['deliveryOrderId']     = $results_my_orders_val['id'];
                $my_orders['orders'][$my_orders_count]['clientOrderId']       = $clientOrderId;
                $my_orders['orders'][$my_orders_count]['addressId']           = $results_my_orders_val['addressId'];
                $my_orders['orders'][$my_orders_count]['clientId']            = $results_my_orders_val['clientId'];
                $my_orders['orders'][$my_orders_count]['deliveryOrderStatus'] = $results_my_orders_val['status'];
                
                //client order
                $orderDishId                                                  = $results_client_orders[0]['orderDishId'];
                $my_orders['orders'][$my_orders_count]['clientOrderId']       = $results_client_orders[0]['id'];
                $my_orders['orders'][$my_orders_count]['orderDishId']         = $results_client_orders[0]['orderDishId'];
                $my_orders['orders'][$my_orders_count]['deliveryFee']         = $results_client_orders[0]['deliveryFee'];
                $my_orders['orders'][$my_orders_count]['tax']                 = $results_client_orders[0]['tax'];
                $my_orders['orders'][$my_orders_count]['total']               = $results_client_orders[0]['total'];
                $my_orders['orders'][$my_orders_count]['clientOrderDate']     = $results_client_orders[0]['creationDate'];
                
                $entity_order_dish = $em->createQuery("SELECT od.id, od.dish, od.note, od.quantity, od.price, od.creationDate, od.updateDate 
                                          FROM SuRestaurantBundle:OrderDish od
                                          WHERE od.id = '$orderDishId' ORDER BY od.id DESC"); 
                $results_order_dish= $entity_order_dish->getResult();//print_r($results_order_dish);
                
                $orderDish                                                    = $results_order_dish[0]['dish'];
                $my_orders['orders'][$my_orders_count]['orderDishDish']       = $results_order_dish[0]['dish'];
                $my_orders['orders'][$my_orders_count]['orderDishNote']       = $results_order_dish[0]['note'];
                $my_orders['orders'][$my_orders_count]['orderDishQuantity']   = $results_order_dish[0]['quantity'];
                $my_orders['orders'][$my_orders_count]['orderDishPrice']      = $results_order_dish[0]['price'];
                $my_orders['orders'][$my_orders_count]['orderDishCDate']      = $results_order_dish[0]['creationDate'];
                $my_orders['orders'][$my_orders_count]['orderDishUDate']      = $results_order_dish[0]['updateDate'];
                
                //Get dish abd shop detail
                $entity_dish_det  = $em->createQuery("SELECT d.name, d.description, d.image, d.category, d.shop 
                                          FROM SuRestaurantBundle:Dish d
                                          WHERE d.dishId = '$orderDish' "); 
                $results_dish_det = $entity_dish_det->getResult();//print_r($results_dish_det);
                $dishShop                                           = $results_dish_det[0]['shop'];
                $my_orders['orders'][$my_orders_count]['dishName']  = $results_dish_det[0]['name'];
                $my_orders['orders'][$my_orders_count]['dishDesc']  = $results_dish_det[0]['description'];
                $my_orders['orders'][$my_orders_count]['dishImg']   = $results_dish_det[0]['image'];
                
                //shop detail
                $entity_shop_det  = $em->createQuery("SELECT s.shopName, s.shopDescription, s.shopLogo
                                          FROM SuRestaurantBundle:Shop s
                                          WHERE s.shopId = '$dishShop' "); 
                $results_shop_det = $entity_shop_det->getResult();//print_r($results_shop_det);
                $my_orders['orders'][$my_orders_count]['shopName']  = $results_shop_det[0]['shopName'];
                $my_orders['orders'][$my_orders_count]['shopDesc']  = $results_shop_det[0]['shopDescription'];
                $my_orders['orders'][$my_orders_count]['shopImg']   = $results_shop_det[0]['shopLogo'];
                
                
                
                $my_orders_count++;
              }
          }
          //print_r($my_orders);die();
          return $my_orders;   
       
        }
        return $my_orders; 
    }
    
    
     /**
     * Generate Order number
     *
     * 
     */
    public function generateRandomString($length = 7) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return "ORD-".$randomString;
    }
    
            
  
    
}
