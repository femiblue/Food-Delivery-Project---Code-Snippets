<?php

/**
 * @author Femi Bello
 * @copyright 2018
 */



namespace Su\FrontOfficeUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Su\RestaurantBundle\Entity\Dish;
use Su\RestaurantBundle\Entity\Shop;
use Su\RestaurantBundle\Entity\Favorites;

class ShopController extends Controller
{

    /**
     * @Route("/", name="front_office_user_homepage")
     * 
     */
    public function shopAction(Request $request)
    { 
       $landingObj   = $this->container->get('landing');
       $results_cat  = $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $results_cate = $landingObj->getCategoriesAction(); //print_r($results_cate);
       $results_fav  = self::subFavoritesAction();//print_r($results_fav);die();
       $cartObj              = $this->container->get('cart');
       $num_cart_items       = $cartObj->getNumberOfCartItemsAction();
       
       return $this->render('SuFrontOfficeUserBundle:Shop:index.html.twig', 
         array(
        'entity_cat'      => $results_cat,
        'entity_fav'      => $results_fav,
        'entity_cate'     => $results_cate,
        'num_cart_items'  => $num_cart_items,
        'page_title'      => "Front Store",
       ));         
    }
    
    /**
     * @Route("/dishes/{id}", name="front_office_user_dishesbycat")
     * 
     */
    public function dishesByCatAction(Request $request)
    { 
       $dish_id          = ""; 
       $dish_cat         = "";
       $dish_conter      = 0;
       $results_dish_arr = array();
       $results_dish_cat = array();
       $results_cdishes  = array();
       $is_favorite      = false;
       $em               = $this->getDoctrine()->getManager();
        
       $landingObj       = $this->container->get('landing');
       $results_cat      = $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $cartObj          = $this->container->get('cart');
       $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
       $results_fav      = self::subFavoritesAction();//print_r($results_fav);die();
       //print_r($request->get('id')); die();
       
       $dish_cat         = $request->get('id');
       $dish_cat         = str_replace('-', ' ',$dish_cat);//replace "-" with " "
       
       /*
       if( ($dish_cat== "") || ((is_int($dish_cat) == false)) ){ //
          return  $this->forward('SuFrontOfficeUserBundle:Shop:index.html.twig');
       }else{
        */  
       //Category Name
       $entity_dish_cat  = $em->createQuery("SELECT c.categoryId, c.name
                                               FROM SuRestaurantBundle:Category c
                                               WHERE c.name LIKE '%$dish_cat%'");
       $results_dish_cat = $entity_dish_cat->getResult();
       // print_r($results_dish_cat);die(); 
       $cat_name =  $results_dish_cat[0]['name'];
       $cat_id   =  $results_dish_cat[0]['categoryId'];
       //get dishes the following category
       $entity_cdishes= $em->createQuery('SELECT d.dishId, d.name, d.description, d.price,
                                          d.image, d.category, d.shop FROM SuRestaurantBundle:Dish d 
                                          WHERE d.category ='.$cat_id.'  ORDER BY d.dishId ASC'); 
       $results_cdishes= $entity_cdishes->getResult();//print_r($results_cdishes);
       foreach($results_cdishes as $results_val){
         $dish_id   = $results_val['dishId'];
         $dish_shop = $results_val['shop'];
         //echo "FAV ID..".$fav_id;echo "<br/>";
          //Check if dish has a discount
           $results_spe = $landingObj->getMainBannersAction($dish_id); 
           //print_r($results_spe); echo "<br/>-------------<br/>";
           if(!empty($results_spe))
           {
            $results_cdishes[$dish_conter]['discount_price'] = $results_spe[0]['specialOfferPrice'];
            
           }else{
           $results_cdishes[$dish_conter]['discount_price'] = 0;
           }
         //check if this dish has been favorited and then set the is_favorite flag
         /*
         if(!empty($results_fav)){
            foreach($results_fav as $results_fav_val){
                if($dish_id == $results_fav_val['dishId']) {
                    $is_favorite = true;
                }       
            }
         }
         */
         $is_favorite                                 = self::isFavoriteAction($dish_id);
         $results_cdishes[$dish_conter]['is_favorite']= $is_favorite['status'];
         $results_cdishes[$dish_conter]['fav_id']     = $is_favorite['fav_id'];
               
         //$results_cdishes[$dish_conter]['isFavorite'] = $is_favorite;
         $results_cdishes[$dish_conter]['snug']       = str_replace(' ', '-',strtolower($results_val['name']));//convert everything lowercase and form array
         
         $entity_dish_shop = $em->createQuery('SELECT s.shopId, s.shopName
                                               FROM SuRestaurantBundle:Shop s
                                               WHERE s.shopId='.$dish_shop.'');
         $results_dish_shop= $entity_dish_shop->getResult();
         foreach($results_dish_shop as $results_dish_val){
             $results_cdishes[$dish_conter]['shopName']   = $results_dish_val['shopName'];
         }
         $dish_conter++;
       }
       //print_r($results_cdishes); die();
       return $this->render('SuFrontOfficeUserBundle:Shop:c_dishes.html.twig', 
         array(
        'entity_dishes'   => $results_cdishes,
        'entity_cat'      => $results_cat,
        'entity_fav'      => $results_fav,
        'entity_cat_name' => $cat_name,
        'num_cart_items'  => $num_cart_items,
        'page_title'      => ucfirst($cat_name),
        
       )); 
       //}      
    }
    
    /**
     * @Route("/dish/{id}", name="front_office_user_dish")
     * 
     */
    public function dishAction($id)
    { 
       //echo "ID...".$id; die();
       //if(($id == "") || (empty($id))){
       //   $this->redirect('front_office_user_homepage');;
       //}else{
        $em = $this->getDoctrine()->getManager();
        $landingObj       = $this->container->get('landing');
        $results_cat      = $landingObj->getTopMenuAction();//print_r($results_cat);die();
        $cartObj          = $this->container->get('cart');
        $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
        $results_dish_val = array();
        $entity_shop      = array();
        $prod_img         = array();
        
        $dish_name   = str_replace('-', ' ',$id);//replace "-" with " "
        //echo "ID...".$dish_name; die();
       
        $entity_dish = $em->createQuery("SELECT d.dishId, d.name, d.description, d.price,
                                          d.image, d.category, d.shop FROM SuRestaurantBundle:Dish d 
                                          WHERE d.name LIKE '%$dish_name%' ORDER BY d.dishId ASC"); 
        $results_dish= $entity_dish->getResult(); 
        if(!empty($results_dish))
        {                                
            $prod_img    = $results_dish[0]['image'];
            $prod_shop   = $results_dish[0]['shop'];
            //$entity_dish = $em->getRepository('SuRestaurantBundle:Dish')->find($dish_name);
            //$prod_img    = $entity_dish->getImage();
            //echo $prod_img;die();
        	//getShop
    		//$entity_shop = $em->getRepository('SuRestaurantBundle:Shop')->find($entity_dish->getShop());
            $entity_shop = $em->getRepository('SuRestaurantBundle:Shop')->find($prod_shop);
            //print_r($entity_shop); die();
            if ((!$entity_dish) || (!$entity_shop)) {
                throw $this->createNotFoundException('Unable to find Dish entity.');
            }
            foreach($results_dish as $results_dish_val)
            {   //do this to remove the first dimension
            
                //Check if dish has a discount
                $results_spe                         = $landingObj->getMainBannersAction($results_dish_val['dishId']); 
                if(!empty($results_spe))
                {
                 $results_dish_val['discount_price'] = $results_spe[0]['specialOfferPrice'];
                }else{
                 $results_dish_val['discount_price'] = 0;
                }
                
                return $this->render('SuFrontOfficeUserBundle:Shop:dish.html.twig', 
                array(
                'entity_dish'    => $results_dish_val,
                'entity_shop'    => $entity_shop,
                'prod_img'       => $prod_img,
                'entity_cat'     => $results_cat,
                'num_cart_items' => $num_cart_items,
                'page_title'     => ucfirst($dish_name),
                ));
            
            }
            
        }else
        { 
               return $this->render('SuFrontOfficeUserBundle:Shop:dish.html.twig', 
               array(
               'entity_dish'    => $results_dish_val,
               'entity_shop'    => $entity_shop,
               'prod_img'       => $prod_img,
               'entity_cat'     => $results_cat,
               'num_cart_items' => $num_cart_items,
               'page_title'     => ucfirst($dish_name),
               ));
            
        }
        //return $this->render('SuFrontOfficeUserBundle:Shop:dish.html.twig');
       //}
       
                
    }
    
    /**
     * @Route("/stores", name="front_office_user_stores")
     * 
     */
    public function storesAction(Request $request)
    { 
       $landingObj       = $this->container->get('landing');
       $results_cat      = $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $results_fav      = self::subFavoritesAction();//print_r($results_fav);die();
       $results_store    = self::getStoresAction();//print_r($results_store);die();
       $cartObj          = $this->container->get('cart');
       $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
       
       return $this->render('SuFrontOfficeUserBundle:Shop:stores.html.twig', 
         array(
        'entity_cat'      => $results_cat,
        'entity_fav'      => $results_fav,
        'entity_store'    => $results_store,
        'num_cart_items'  => $num_cart_items,
        'page_title'      => "Available Stores",
       ));         
    }
    
    //Get Stores
    public function getStoresAction(){
        
         $shop_cnt        = 0;
         $em              = $this->getDoctrine()->getManager();
         //Use this for the top menu
         $entity_stores   = $em->createQuery('SELECT s.shopId, s.shopName, s.shopDescription, s.shopLogo,
                                              s.shopLocation, s.shopZipcode FROM SuRestaurantBundle:Shop s 
                                              ORDER BY s.shopId ASC'); 
         $results_stores  = $entity_stores->getResult();//print_r($results_stores);
         foreach($results_stores as $results_stores_val)
         {
           //echo "Shop Name..".$results_stores_val['shopName']; echo "<br/><br/>";
           if(strpos($results_stores_val['shopName'],"&")) //Check if "&" exist in string
           {
             $results_stores_val['shopName'] = trim(substr($results_stores_val['shopName'],0,strpos($results_stores_val['shopName'],"&"))); //Truncate when & is encountered
           }
           //echo "Shop Name1..".$results_stores_val['shopName']; echo "<br/><br/>";
           $results_stores[$shop_cnt]['snug'] = preg_replace('/-+/', '-',str_replace(' ', '-',strtolower($results_stores_val['shopName'])));//convert everything lowercase and form array    
           $shop_cnt++;
         }
         return $results_stores;
    }   
    
    /**
     * @Route("/stores/dishes/{id}", name="front_office_user_dishesbystore")
     * 
     */
    public function dishesByStoreAction(Request $request)
    { 
       $dish_id            = ""; 
       $dish_store         = "";
       $dish_conter        = 0;
       $is_favorite        = false;
       $results_dish_arr   = array();
       $results_dish_store = array();
       $results_sdishes    = array();
       
       $em                 = $this->getDoctrine()->getManager();
        
       $landingObj = $this->container->get('landing');
       $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $cartObj            = $this->container->get('cart');
       $num_cart_items     = $cartObj->getNumberOfCartItemsAction();
       $results_fav        = self::subFavoritesAction();//print_r($results_fav);die();
       $dish_store         = $request->get('id');
       $dish_store         = str_replace('-', ' ',$dish_store);//replace "-" with " "
            
       //Store Name
       $entity_dish_store  = $em->createQuery("SELECT s.shopId, s.shopName
                                               FROM SuRestaurantBundle:Shop s
                                               WHERE s.shopName LIKE '%$dish_store%' ");
       $results_dish_store = $entity_dish_store->getResult();
       // print_r($results_dish_store);die(); 
       $shop_name          =  $results_dish_store[0]['shopName'];
       $shop_id            =  $results_dish_store[0]['shopId'];       
       //get dishes the following category
       $entity_sdishes     = $em->createQuery('SELECT d.dishId, d.name, d.description, d.price,
                                               d.image, d.category, d.shop FROM SuRestaurantBundle:Dish d 
                                               WHERE d.shop ='.$shop_id.'  ORDER BY d.dishId ASC'); 
       $results_sdishes= $entity_sdishes->getResult();//print_r($results_sdishes);
       foreach($results_sdishes as $results_val){
         $dish_id   = $results_val['dishId'];
         $dish_shop = $results_val['shop'];
         $results_sdishes[$dish_conter]['snug']       = preg_replace('/-+/', '-',str_replace('&', '',str_replace(' ', '-',strtolower($results_val['name']))));//convert everything lowercase and form array
         //check if this dish has been favorited and then set the is_favorite flag
         if(!empty($results_fav)){
            foreach($results_fav as $results_fav_val){
                if($dish_id == $results_fav_val['dishId']) {
                    $is_favorite = true;
                }       
            }
         }
         $results_sdishes[$dish_conter]['isFavorite'] = $is_favorite;
         $results_sdishes[$dish_conter]['shopName']   = $shop_name;
       
       
         $dish_conter++;
       }
       //print_r($results_sdishes); die();
       return $this->render('SuFrontOfficeUserBundle:Shop:s_dishes.html.twig', 
         array(
        'entity_dishes'    => $results_sdishes,
        'entity_cat'       => $results_cat,
        'entity_fav'       => $results_fav,
        'entity_shop_name' => $shop_name,
        'num_cart_items'   => $num_cart_items,
        'page_title'       => ucfirst($shop_name),
       ));         
    }
    
   /**
     * @Route("/favorites", name="front_office_user_favorites")
     * 
     */
    public function favoritesAction(Request $request)
    {  
       $user      = $this->getUser(); //use current user to pick favorite 
       //print_r($user); die(); 
       $fav_conter=0;
       $userId    = $user->getId();
       $em        = $this->getDoctrine()->getManager();
       $landingObj = $this->container->get('landing');
       $results_cat= $landingObj->getTopMenuAction();//print_r($results_cat);die();
       $cartObj            = $this->container->get('cart');
       $num_cart_items     = $cartObj->getNumberOfCartItemsAction();
       
       $entity_favorites = $em->createQuery('SELECT f.favoriteId, f.favoriteClient, f.favoriteDish 
                                             FROM SuRestaurantBundle:Favorites f
                                             WHERE f.favoriteClient='.$userId.' ORDER BY f.favoriteId DESC');
       $results_fav      = $entity_favorites->getResult();
     
           
       //print_r($results_fav);
       //loop through the results and merge with corresponding dishes
       foreach($results_fav as $results_val){
         $fav_id    = $results_val['favoriteId'];;
         $fav_dish  = $results_val['favoriteDish'];
         

         //echo "FAV ID..".$fav_id;echo "<br/>";
         $entity_fav_dish = $em->createQuery('SELECT d.dishId, d.name, d.description, d.price, d.image, d.shop
                                               FROM SuRestaurantBundle:Dish d
                                               WHERE d.dishId='.$fav_dish.'');
         $results_fav_dish = $entity_fav_dish->getResult();
         foreach($results_fav_dish as $results_fav_val){
             
             //Check if dish has a discount
             $results_spe                         = $landingObj->getMainBannersAction($fav_dish); 
             if(!empty($results_spe))
             {
              $results_fav_val['discount_price'] = $results_spe[0]['specialOfferPrice'];
             }else{
              $results_fav_val['discount_price'] = 0;
             }
            
             $results_fav_val['snug']       = str_replace(' ', '-',strtolower($results_fav_val['name']));//convert everything lowercase and form array
             $results_fav_val['favoriteId'] = $fav_id;
             $results_fav[$fav_conter]      = $results_fav_val;
             
             
         }

         $fav_conter++;
       }

       //print_r($results_fav);
       return $this->render('SuFrontOfficeUserBundle:Shop:favorites.html.twig', 
         array(
        'entity_cat'      => $results_cat,
        'entity_fav'      => $results_fav,
        'num_cart_items'  => $num_cart_items,
        'page_title'      => "My Favorites",
       ));         
    }
    
    
    //@Route("/add/favorite/{id}", name="front_office_user_addfavorite")
    /**
     * @Route("/add/favorite", name="front_office_user_addtofavorites")
     * @Method("POST")
     */
    public function addFavoriteAction(Request $request)
    {  
       $session      = new Session();
       $user         = $this->getUser(); //use current user to pick favorite 
       $fav_dish     = $request->get('id');
       $current_date = new \DateTime("now"); //date("Y-m-d H:i:s");
       //print_r($user); die(); 
       if((!empty($user)) && ($fav_dish != "")){ //do only for logged in users
           $userId   = $user->getId();
           $favorite = new Favorites();
           $favorite->setFavoriteClient($userId);
           $favorite->setFavoriteDish($fav_dish);
           $favorite->setFavoriteCreatedate($current_date);
           if($favorite){
               $em = $this->getDoctrine()->getManager();
               $em->persist($favorite);
               $em->flush();
           }
           $session->getFlashBag()->add('success', "Item Added to Favorites Successfully");
           return $this->redirect($request->headers->get('referer'));
           
        }else{
            $session->getFlashBag()->add('error', "Unable to add Favorite Item");
            return $this->redirect($request->headers->get('referer'));
            //return $this->redirect($this->generateUrl('front_office_user_homepage'));
        }        
    }
    
    
    /**
     * @Route("/remove/favorite", name="front_office_user_removefromfavorite")
     * @Method("POST")
     */
    public function removeFavoriteAction(Request $request)
    {  
       $session      = new Session();
       $user         = $this->getUser(); //use current user to pick favorite 
       $fav_id       = $request->get('id');
       $current_date = new \DateTime("now"); //date("Y-m-d H:i:s");
       $em           = $this->getDoctrine()->getManager();
       $queryBuilder = $em->createQueryBuilder();
       //print_r($user); die();         
       //do only for logged in users and if fav_id was passed
       if((!empty($user)) && ($fav_id != ""))
       {
           $q                      = $queryBuilder
                                     ->delete('SuRestaurantBundle:Favorites', 'f')
                                     ->where($queryBuilder->expr()->eq('f.favoriteId', $fav_id))
                                     ->getQuery();
                                     
           $p                      = $q->execute();
           
            $session->getFlashBag()->add('success', "Item Removed from Favorites Successfully");
            return $this->redirect($request->headers->get('referer')); 
       }else
       {
        
           $session->getFlashBag()->add('error', "Unable to Remove Favorite Item");
           return $this->redirect($request->headers->get('referer')); 
        
       }       
    }
    
     /**
     * @Route("/sub/favorites", name="front_office_user_subfavorites")
     * 
     */
    public function subFavoritesAction()
    { 
       $landingObj      = $this->container->get('landing');
       $fav_id          = ""; 
       $fav_dish        = "";
       $fav_counter     = 0;
       $results_fav_arr = array();
       $results_fav_dish= array();
       $results_fav     = array();
       
       $user       = $this->getUser(); //use current user to pick favorite 
       //print_r($user); die(); 
       if(!empty($user)){
           $userId = $user->getId(); //echo "user..".$userId;die();
           $limit_fav  = 4; 
           $em         = $this->getDoctrine()->getManager();
           $entity_favorites = $em->createQuery('SELECT f.favoriteId, f.favoriteClient, f.favoriteDish
                                                 FROM SuRestaurantBundle:Favorites f
                                                 WHERE f.favoriteClient='.$userId.' ORDER BY f.favoriteId DESC');
                                                 $entity_favorites->setMaxResults($limit_fav);
           $results_fav_arr  = $entity_favorites->getResult();
           
           //print_r($results_fav);
           //loop through the results and merge with corresponding dishes
           foreach($results_fav_arr as $results_val){
             $fav_id   = $results_val['favoriteId'];
             $fav_dish = $results_val['favoriteDish'];
             
             
             //echo "FAV ID..".$fav_id;echo "<br/>";
             $entity_fav_dish = $em->createQuery('SELECT d.dishId, d.name, d.description, d.price, d.image, d.shop
                                                   FROM SuRestaurantBundle:Dish d
                                                   WHERE d.dishId='.$fav_dish.'');
             $results_fav_dish = $entity_fav_dish->getResult();
             foreach($results_fav_dish as $results_fav_val){
                 //Check if dish has a discount
                 $result_spe  = $landingObj->getMainBannersAction($fav_dish);
                 if(!empty($result_spe))
                 {
                   $results_fav_val['discount_price'] = $result_spe[0]['specialOfferPrice'];
            
                 }else{
                   $results_fav_val['discount_price'] = 0;
                 }
                 //use a friendly url for each dish instead of dish id
                 $results_fav_val['snug']            = preg_replace('/-+/', '-',str_replace('&', '',str_replace(' ', '-',strtolower($results_fav_val['name']))));//convert everything lowercase and form array
                 
                 $results_fav_val['favoriteId'] = $fav_id;
                 $results_fav[$fav_counter]      = $results_fav_val;
             }
             $fav_counter++;
           }
       } //end if
       return $results_fav;         
    }
    
    //Check if a dish is cheked as favorite
    public function isFavoriteAction($id)
    {  
       $user       = $this->getUser(); //use current user to pick favorite 
       
        if(!empty($user))
        {
           $userId = $user->getId(); //echo "user..".$userId;die();
           $em         = $this->getDoctrine()->getManager();
           $entity_favorites = $em->createQuery('SELECT f.favoriteId, f.favoriteClient, f.favoriteDish
                                                 FROM SuRestaurantBundle:Favorites f
                                                 WHERE f.favoriteClient='.$userId.' AND f.favoriteDish='.$id.' ORDER BY f.favoriteId DESC');
           $results_fav_arr  = $entity_favorites->getResult();
           if(!empty($results_fav_arr))
           {
             $myreturn['status'] = true;
             $myreturn['fav_id'] = $results_fav_arr[0]['favoriteId'];
             return $myreturn;
           }else
           {
             $myreturn['status'] = false;
             $myreturn['fav_id'] = "";
             return $myreturn;
           }
           
        }else
        {
           $myreturn['status'] = false;
           $myreturn['fav_id'] = "";
           return $myreturn; 
        }
       
       
    }

    
    /**
     * Get a number dishes randomly to display
     * 
     */
    public function getRandomDishesAction($no_of_records)
    {  
       $dish_conter           = 0;
       $results_dish_arr      = array();
       $results_random_dishes = array();
       $landingObj            = $this->container->get('landing');

       if($no_of_records > 0){
           $em         = $this->getDoctrine()->getManager();
          
           $entity_rand_dish = $em->createQuery('SELECT d.dishId, d.name, d.description, d.price, d.image, d.shop
                                                FROM SuRestaurantBundle:Dish d ');
                                                //$entity_rand_dish->setMaxResults($no_of_records);
           $results_dish_arr = $entity_rand_dish->getResult();
           //print_r($results_dish_arr);
           shuffle($results_dish_arr); //randomize
           
           foreach($results_dish_arr as $results_dish_arr_val)
           {
             if( $dish_conter <= ($no_of_records-1) )
             {
               //Check if each dish is favorited
               $is_favorite                         = self::isFavoriteAction($results_dish_arr_val['dishId']);
               $results_dish_arr_val['is_favorite'] = $is_favorite['status'];
               $results_dish_arr_val['fav_id']      = $is_favorite['fav_id'];
               
               //Check if dish has a discount
               $results_spe = $landingObj->getMainBannersAction($results_dish_arr_val['dishId']); 
               //print_r($results_spe); echo "<br/>-------------<br/>";
               if(!empty($results_spe))
               {
                $results_dish_arr_val['discount_price'] = $results_spe[0]['specialOfferPrice'];
                
               }else{
               $results_dish_arr_val['discount_price'] = 0;
               }
               //use a friendly url for each dish instead of dish id
               $results_dish_arr_val['snug']        = preg_replace('/-+/', '-',str_replace('&', '',str_replace(' ', '-',strtolower($results_dish_arr_val['name']))));//convert everything lowercase and form array 
               $results_random_dishes[$dish_conter] = $results_dish_arr_val;
               $dish_conter++;
             }
           }
       } //end if
       //echo "<br/><br/>";
       //print_r($results_random_dishes);
       return $results_random_dishes;         
    }
}
