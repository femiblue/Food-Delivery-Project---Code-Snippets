<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

//Store Front
$collection->add('front_office_user_homepage', new Route('/', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:shop',
)));

//Dish Detail
$collection->add('front_office_user_dish', new Route('/dish/{id}', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:dish',
)));

//Display all Stores
$collection->add('front_office_user_stores', new Route('/stores', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:stores',
)));

//Dishes in a Store
$collection->add('front_office_user_dishesbystore', new Route('/stores/dishes/{id}', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:dishesByStore',
)));

//Dishes in a Dish category
$collection->add('front_office_user_dishesbycat', new Route('/dishes/{id}', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:dishesByCat',
)));

//Favorites
$collection->add('front_office_user_favorites', new Route('/favorites', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:favorites',
)));

//Add a Favorite
$collection->add('front_office_user_addtofavorites', new Route('/add/favorite/', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:addFavorite',
)));
/*$collection->add('front_office_user_afavorites', new Route('/add/favorite/{id}', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:addFavorite',
)));*/

//Remove from Favorite
$collection->add('front_office_user_removefromfavorite', new Route('/remove/favorite/', array(
    '_controller' => 'SuFrontOfficeUserBundle:Shop:removeFavorite',
)));

//Add a Dish to Cart
$collection->add('front_office_user_addtocart', new Route('/add-to-cart', array(
    '_controller' => 'SuFrontOfficeUserBundle:ShoppingCart:addToBasket',
)));

//Display all items in the Cart
$collection->add('front_office_user_basket', new Route('/shopping-cart', array(
    '_controller' => 'SuFrontOfficeUserBundle:ShoppingCart:index',
)));

//Clear all Item from the shopping cart
$collection->add('front_office_user_clearcart', new Route('/clear-cart', array(
    '_controller' => 'SuFrontOfficeUserBundle:ShoppingCart:clearBasket',
)));

//Remove an item from shopping cart
$collection->add('front_office_user_removeitemfromcart', new Route('/rem-from-cart/{id}', array(
    '_controller' => 'SuFrontOfficeUserBundle:ShoppingCart:removeItemFromBasket',
)));

//Update cart item when qty is changed
$collection->add('front_office_user_update_cart_item', new Route('/update-cart-item', array(
    '_controller' => 'SuFrontOfficeUserBundle:ShoppingCart:updateCartItem',
)));

//Display Billing Addresse, New Address Form and Cart underneath
$collection->add('front_office_user_address', new Route('/billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:index',
)));

//Add a new Billing Address
$collection->add('front_office_user_createaddress', new Route('/add-billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:addBillingAddress',
)));

//Set as Billing Address
$collection->add('front_office_user_setaddress', new Route('/set-billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:setBillingAddress',
)));

//Edit Billing Address
$collection->add('front_office_user_editaddress', new Route('/edit-billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:editBillingAddress',
)));

//Update Billing Address
$collection->add('front_office_user_updateaddress', new Route('/update-billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:updateBillingAddress',
)));

//Delete Address
$collection->add('front_office_user_deleteaddress', new Route('/delete-billing-address', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:deleteBillingAddress',
)));

//Place Order
$collection->add('front_office_user_placeorder', new Route('/place-order', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:placeOrder',
)));

//Confirm Order
$collection->add('front_office_user_confirmorder', new Route('/confirm-order', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:confirmOrder',
)));

//My Orders
$collection->add('front_office_user_orders', new Route('/orders', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:myOrders',
)));

//Orders History
$collection->add('front_office_user_ordershistory', new Route('/orders-history', array(
    '_controller' => 'SuFrontOfficeUserBundle:CheckOut:ordersHistory',
)));

//Payment
$collection->add('front_office_user_payment', new Route('/payment', array(
    '_controller' => 'SuFrontOfficeUserBundle:Payment:payment',
)));

/*
$collection->add('front_office_user_login_check', new Route('/logincheck', array(
    '_controller' => 'SuFrontOfficeUserBundle:Login:logincheck',
)));
*/
return $collection;
