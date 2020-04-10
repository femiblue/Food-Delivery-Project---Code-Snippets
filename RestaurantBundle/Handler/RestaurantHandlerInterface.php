<?php
/**
 * Created by PhpStorm.
 * User: TECH_2015-12-01
 * Date: 27/09/16
 * Time: 09:23 AM
 */

namespace Su\RestaurantBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager as Entity;


interface RestaurantHandlerInterface
{
    /**
     * Get a Entity given the identifier
     *
     * @api
     *
     * @param mixed $id
     *
     * @return Entity
     */
    public function get($id);
    /**
     * Get a list of Entities.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0);
    /**
     * Post Entity, creates a new Entity.
     *
     * @api
     *
     * @param array $parameters
     * @param string $entityName
     *
     * @return Entity
     */
    public function post(array $parameters,$entityName='');
    /**
     * Edit a Entity.
     *
     * @api
     *
     * @param Entity   $page
     * @param array           $parameters
     * @param string $entityName
     *
     * @return Entity
     */
    public function put($page, array $parameters,$entityName='');
    /**
     * Partially update a Entity.
     *
     * @api
     *
     * @param Entity   $page
     * @param array           $parameters
     * @param string $entityName
     *
     * @return Entity
     */
    public function patch($page, array $parameters,$entityName='');

}