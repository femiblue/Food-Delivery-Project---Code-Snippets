<?php

namespace Su\FrontOfficeBundle\Controller;

use Su\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Su\RestaurantBundle\Entity\category;
use Su\RestaurantBundle\Entity\Specialoffers;
use Su\RestaurantBundle\Entity\Dish;
use Su\RestaurantBundle\Entity\Shop;
use Su\RestaurantBundle\Entity\Favorites;

class LandingController extends Controller
{
    public function indexAction()
    {    
         $results_cat     = self::getTopMenuAction();//print_r($results_cat);
         $results_spe     = self::getMainBannersAction(0);//print_r($results_spe);
         $results_cate    = self::getCategoriesAction(); //print_r($results_cate);
         $shopObj         = $this->container->get('shop');
         $results_fav     = $shopObj->subFavoritesAction();//print_r($results_fav);die();
         $results_rand    = $shopObj->getRandomDishesAction(8); //print_r($results_rand);
         $fav_counter     = 0;
         $results_fav_new = array();
         //print_r($results_fav);
         
         $cartObj          = $this->container->get('cart');
         $num_cart_items   = $cartObj->getNumberOfCartItemsAction();
         
         /*
         $fav_rec_count    = count($results_fav);
         foreach($results_fav as $results_fav_val)
         {
           //Check if dish has a discount
           $fav_dish_id = $results_fav_val['dishId'];
           $result_spe  = self::getMainBannersAction($fav_dish_id); 
           if(!empty($result_spe))
           {
            $results_fav_val['discount_price'] = $result_spe[0]['specialOfferPrice'];
            
           }else{
            $results_fav_val['discount_price'] = 0;
           }
           //use a friendly url for each dish instead of dish id
           $results_fav_val['snug']            = preg_replace('/-+/', '-',str_replace('&', '',str_replace(' ', '-',strtolower($results_fav_val['name']))));//convert everything lowercase and form array
           $results_fav_new[$fav_counter]      = $results_fav_val;
           $fav_counter++;
         }
         */
         return $this->render('SuFrontOfficeBundle:Landing:index.html.twig', 
         array(
         'entity_cat'      => $results_cat,
         'entity_specials' => $results_spe,
         'entity_fav'      => $results_fav,
         'entity_rand'     => $results_rand,
         'entity_cate'     => $results_cate,
         'num_cart_items'  => $num_cart_items,
         'page_title'      => "Welcome",
  
        ));
    }
    
    //Get top menu
    public function getTopMenuAction(){
        
         $limit_cat       = 6;
         $snug            = array();
         $cat_cnt         = 0;
         $main_cnt        = 0;
         $sub_cnt         = 0;
         $main_cnt_chk    = 3;
         $main_top_menu   = array();
         $sub_top_menu    = array();
         $em              = $this->getDoctrine()->getManager();
         //Use this for the top menu
         $entity_cat      = $em->createQuery('SELECT c.categoryId, c.name FROM SuRestaurantBundle:Category c 
                                              ORDER BY c.categoryId ASC'); 
                            $entity_cat->setMaxResults($limit_cat);
         $results_cat     = $entity_cat->getResult();//print_r($results_cat);
         //attach a snug to each category
         foreach($results_cat as $results_cat_val)
         {  
            if($cat_cnt <= $main_cnt_chk)
            {
             $main_top_menu[$main_cnt] = $results_cat_val;   
             $main_top_menu[$main_cnt]['snug'] = str_replace(' ', '-',strtolower($results_cat_val['name']));//convert everything lowercase and form array
             $main_cnt++;
            }
            if($cat_cnt > $main_cnt_chk)
            {
             $sub_top_menu[$sub_cnt] = $results_cat_val;    
             $sub_top_menu[$sub_cnt]['snug'] = str_replace(' ', '-',strtolower($results_cat_val['name']));//convert everything lowercase and form array
             $sub_cnt++;
            }
            
            $cat_cnt++;
         }
         $results_cat         = array();
         $results_cat['main'] = $main_top_menu;
         $results_cat['sub']  = $sub_top_menu;
         return $results_cat;
    }   
    
    //Get Main banners
    public function getMainBannersAction($id){
        
         $dish_conter     = 0;
         $dish_id         = $id;
         $em              = $this->getDoctrine()->getManager();
         if($dish_id > 0)
         {
            $dish_filter = "AND so.specialOfferDish = $dish_id";
         }else
         {
            $dish_filter = "";
         }
         
          //Use this for the rolling banners
         $entity_specials = $em->createQuery("SELECT so.specialOfferId, so.specialOfferTitle, so.specialOfferDescription, so.specialOfferPrice,
                                              so.specialOfferDish, so.specialOfferBanner,so.specialOfferStatus FROM SuRestaurantBundle:Specialoffers so
                                              WHERE so.specialOfferStatus>=1 $dish_filter ORDER BY so.specialOfferId DESC");
         $results_spe     = $entity_specials->getResult();
         foreach($results_spe as $results_val)
         {
            $entity_dish                       = $em->getRepository('SuRestaurantBundle:Dish')->find($results_val['specialOfferDish']);
            $prod_name                         = $entity_dish->getName();
            $prod_price                        = $entity_dish->getPrice();  
            $results_spe[$dish_conter]['snug'] = preg_replace('/-+/', '-',str_replace('&', '',str_replace(' ', '-',strtolower($prod_name))));//convert everything lowercase and form array
            $results_spe[$dish_conter]['orig_price'] = $prod_price;
            $dish_conter++;
         }
         return $results_spe;
    }  
    
    //Get Categories
  
    public function getCategoriesAction(){
        
         $results_cat     = array();
         $results_cat_arr = array();
         $cat_conter      = 0;
         $results_cat_val = array();
         $cat_id          = "";
         $cat_name        = "";
         $cat_cnt         = 0;
         $results_cat_dish= array();
        
         $em              = $this->getDoctrine()->getManager();
         //Use this for Dish categories
         $entity_cat      = $em->createQuery('SELECT c.categoryId, c.name FROM SuRestaurantBundle:Category c 
                                              ORDER BY c.categoryId ASC'); 
         $results_cat_arr     = $entity_cat->getResult();//print_r($results_cat_arr);
       
         //loop through the results and merge with corresponding dishes - for appropriate dishes
         foreach($results_cat_arr as $results_val){
           $cat_id   = $results_val['categoryId'];
           $cat_name = $results_val['name'];
           $cat_snug = str_replace(' ', '-',strtolower($cat_name));//convert everything lowercase and form array
           //echo "FAV ID..".$fav_id;echo "<br/>";
           $entity_cat_dish = $em->createQuery('SELECT d.dishId, d.name, d.description, d.price, d.image, d.shop
                                                FROM SuRestaurantBundle:Dish d
                                                WHERE d.category='.$cat_id.' ORDER BY d.dishId DESC');
           $results_cat_dish = $entity_cat_dish->getResult();
           foreach($results_cat_dish as $results_cat_val){
             $results_cat_val['categoryId'] = $cat_id;
             $results_cat_val['catName']    = $cat_name;
             $results_cat_val['snug']       = $cat_snug;
             $results_cat[$cat_conter]      = $results_cat_val;
           }
           $cat_conter++;
         }
         return $results_cat;
    }    
     
}
