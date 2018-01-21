<?php

namespace eCamp\Core\Plugin;

use Doctrine\ORM\EntityManager;
use eCamp\Core\Entity\EventPlugin;
use eCamp\Lib\Acl\Acl;
use eCamp\Lib\Service\BaseService;
use Zend\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

abstract class BasePluginService extends BaseService
{

    /** @var string */
    private $eventPluginId;

    /** @var EventPlugin */
    private $eventPlugin;

    public function __construct
    ( Acl $acl
    , EntityManager $entityManager
    , HydratorInterface $hydrator
    , $entityClassName
    , $eventPluginId
    ) {
        parent::__construct($acl, $entityManager, $hydrator, $entityClassName);

        $this->eventPluginId = $eventPluginId;
    }


    /** @return string */
    protected function getEventPluginId() {
        return $this->eventPluginId;
    }

    /** @return EventPlugin */
    protected function getEventPlugin() {
        if ($this->eventPlugin == null) {
            if ($this->eventPluginId != null) {
                $this->eventPlugin = $this->findEntity(EventPlugin::class, $this->eventPluginId);
            }
        }
        return $this->eventPlugin;
    }


    protected function findEntityQueryBuilder($className, $id) {
        $q = parent::findEntityQueryBuilder($className, $id);

        if (is_subclass_of($className, BasePluginEntity::class)) {
            if ($this->eventPluginId !== null) {
                $q->andWhere('row.eventPlugin = :eventPluginId');
                $q->setParameter('eventPluginId', $this->eventPluginId);
            }
        }

        return $q;
    }

    protected function findCollectionQueryBuilder($className) {
        $q = parent::findCollectionQueryBuilder($className);

        if (is_subclass_of($className, BasePluginEntity::class)) {
            if ($this->eventPluginId !== null) {
                $q->andWhere('row.eventPlugin = :eventPluginId');
                $q->setParameter('eventPluginId', $this->eventPluginId);
            }
        }

        return $q;
    }

    /**
     * @param string $className
     * @return BasePluginEntity|ApiProblem
     */
    protected function createEntity($className) {
        /** @var BasePluginEntity $entity */
        $entity = parent::createEntity($className);

        if ($entity instanceof ApiProblem) {
            return $entity;
        }

        if ($this->getEventPlugin() != null) {
            $entity->setEventPlugin($this->getEventPlugin());
        }

        return $entity;
    }

}
