<?php
/**
 * Created by PhpStorm.
 * User: TECH_2015-12-01
 * Date: 27/09/16
 * Time: 11:15 AM
 */

namespace Su\RestaurantBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
//use Su\RestaurantBundle\Entity\ClientAddress;
//use Su\RestaurantBundle\Entity\OrderDish;
use Su\RestaurantBundle\Exception\InvalidFormException;
//use Su\RestaurantBundle\Form\ClientAddressType;
//use Su\RestaurantBundle\Form\OrderDishType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class RestaurantHandler implements RestaurantHandlerInterface
{
    private $om;
    private $entityClass;
    private $repository;
    private $formFactory;
    private $entityFormType;

    /**
     * @param ObjectManager $om
     * @param $entityClass
     * @param $entityFormType
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ObjectManager $om, $entityClass, $entityFormType, FormFactoryInterface $formFactory)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->formFactory = $formFactory;
        $this->entityFormType = $entityFormType;
    }

    /**
     * Get a Page.
     *
     * @param mixed $id
     *
     * @return ObjectManager
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }
    /**
     * Get a list of Pages.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0)
    {
        return $this->repository->findBy(array(), null, $limit, $offset);
    }
    /**
     * Create a new Page.
     *
     * @param array $parameters
     * @param string $entityName
     *
     *
     * @return ObjectManager
     */
    public function post(array $parameters,$entityName='')
    {

        $page = $this->createPage();
        /*
        if($entityName != ''){
            switch($entityName){
                case 'ClientOrder':
                    return $this->processClientOrderForm($page, $parameters, 'POST');
                    break;

            }
        }
        */
        return $this->processForm($page, $parameters, 'POST');
    }
    /**
     * Edit a Page.
     *
     * @param ObjectManager $page
     * @param array         $parameters
     * @param string $entityName
     *
     * @return ObjectManager
     */
    public function put($page, array $parameters,$entityName='')
    {
        /*
        if($entityName != ''){
            switch($entityName){
                case 'ClientOrder':
                    return $this->processClientOrderForm($page, $parameters, 'PUT');
                    break;

            }
        }*/
        return $this->processForm($page, $parameters, 'PUT');
    }
    /**
     * Partially update a Page.
     *
     * @param ObjectManager $page
     * @param array         $parameters
     * @param string $entityName
     *
     * @return ObjectManager
     */
    public function patch($page, array $parameters,$entityName='')
    {
        /*if($entityName != ''){
            switch($entityName){
                case 'ClientOrder':
                    return $this->processClientOrderForm($page, $parameters, 'PATCH');
                    break;

            }
        }*/
        return $this->processForm($page, $parameters, 'PATCH');
    }

    /**
     * Processes the form.
     *
     * @param $page
     * @param array $parameters
     * @param String $method
     *
     * @return $page
     *
     * @throws \Su\RestaurantBundle\Exception\InvalidFormException
     */
    private function processForm($page, array $parameters, $method = "PUT")
    {

        $form = $this->formFactory->create(new $this->entityFormType, $page, array('method' => $method));
        //dump($form);die;
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $page = $form->getData();
            $this->om->persist($page);
            $this->om->flush($page);
            return $page;
        }
        //throw new InvalidFormException('')
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    /**
     * Processes the form.
     *
     * @param $page
     * @param array $parameters
     * @param String $method
     *
     * @return $page
     *
     * @throws \Su\RestaurantBundle\Exception\InvalidFormException
     */
/*
    private function processClientOrderForm($page, array $parameters, $method = "PUT")
    { //I have disbaled this part from the front. Using a segmented instead of a bulk approach
        //dump($parameters);die();
        $postRequest  =  $parameters;
        $postOrderDish     = $postRequest['orderDish'];
        unset($postRequest['orderDish']);
        $postClientAddress = $postRequest ['clientAddress'];
        unset($postRequest['clientAddress']);

        //dump($postRequest);die();
        $formAddress = $this->formFactory->create(new ClientAddressType(), new ClientAddress(), array('method' => $method));
        $formOrder   = $this->formFactory->create(new OrderDishType(), new OrderDish(), array('method' => $method));
        $form        = $this->formFactory->create(new $this->entityFormType, $page, array('method' => $method));

        $formAddress->submit($postClientAddress, 'PATCH' !== $method);
        $formOrder->submit($postOrderDish, 'PATCH' !== $method);
        $form->submit($postRequest, 'PATCH' !== $method);
        //
        if ($form->isValid() && $formAddress->isValid() && $formOrder->isValid() ) {
            //
            $clientAddress  = $formAddress->getData();
            $clientOrder    = $formOrder->getData();
            $clientOrder_pd = $form->getData();
            $delivery       = new DeliveryOrder();

            //
            $this->om->persist($clientAddress);
            $this->om->flush($clientAddress);

            //
            $clientOrder->setClientAddress($clientAddress);
            $this->om->persist($clientOrder);
            $this->om->flush($clientOrder);

            //
            $clientOrder_pd->setOrderDish($clientOrder);
            $this->om->persist($clientOrder_pd);
            $this->om->flush($clientOrder_pd);

            //Update delivery order
            $delivery->setAddress($clientAddress);
            if( !empty( $clientAddress->getClientId() ) )
                $delivery->setClient($clientAddress->getClientId());
            $delivery->setClientOrder($clientOrder_pd);
            $this->om->persist($delivery);
            $this->om->flush();

            //$this->om->persist($page);
            //$this->om->flush($page);
            return $clientOrder_pd;
        }
        //throw new InvalidFormException('')
        throw new InvalidFormException('Invalid submitted data', $form);
    }
*/
    /**
     * @return mixed
     */
    private function createPage()
    {
        return new $this->entityClass();
    }

}