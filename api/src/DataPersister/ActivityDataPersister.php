<?php

namespace App\DataPersister;

use App\DataPersister\Util\AbstractDataPersister;
use App\DataPersister\Util\DataPersisterObservable;
use App\Entity\Activity;
use App\Entity\ContentNode\ColumnLayout;
use App\Entity\ContentType;
use App\Util\EntityMap;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDataPersister extends AbstractDataPersister {
    public function __construct(
        DataPersisterObservable $dataPersisterObservable,
        private EntityManagerInterface $em,
    ) {
        parent::__construct(
            Activity::class,
            $dataPersisterObservable,
        );
    }

    /**
     * @param Activity $data
     */
    public function beforeCreate($data): Activity {
        $data->camp = $data->category?->camp;

        if (!isset($data->category?->rootContentNode)) {
            throw new \UnexpectedValueException('Property rootContentNode of provided category is null. Object of type '.ColumnLayout::class.' expected.');
        }

        if (!is_a($data->category->rootContentNode, ColumnLayout::class)) {
            throw new \UnexpectedValueException('Property rootContentNode of provided category is of wrong type. Object of type '.ColumnLayout::class.' expected.');
        }

        $rootContentNode = new ColumnLayout();
        $rootContentNode->contentType = $this->em
            ->getRepository(ContentType::class)
            ->findOneBy(['name' => 'ColumnLayout'])
        ;
        $data->setRootContentNode($rootContentNode);

        // deep copy from category root node
        $entityMap = new EntityMap();
        $rootContentNode->copyFromPrototype($data->category->rootContentNode, $entityMap);

        return $data;
    }

    /**
     * @param Activity $data
     */
    public function beforeRemove($data): ?Activity {
        // Deleting rootContentNode would normally be done automatically with orphanRemoval:true
        // However, this currently runs into an error due to https://github.com/doctrine-extensions/DoctrineExtensions/issues/2510

        $this->em->remove($data->rootContentNode);

        return null;
    }
}
