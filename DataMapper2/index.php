<?php
namespace Doctrine\ORM;

use Closure, Exception,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\Connection,
    Doctrine\DBAL\LockMode,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Mapping\ClassMetadataFactory,
    Doctrine\ORM\Proxy\ProxyFactory;

/**
 * The EntityManager is the central access point to ORM functionality.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class EntityManager
{

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     * No query is executed before this method is called from client code.
     *
     * @throws Doctrine\ORM\OptimisticLockException If a version check on an entity that
     *         makes use of optimistic locking fails.
     */
    public function flush()
    {
        $this->_errorIfClosed();
        $this->_unitOfWork->commit();
    }

    /**
     * Finds an Entity by its identifier.
     * This method is often combined with query-oriented ones.
     *
     * @param string $entityName  the class name
     * @param mixed $identifier   usually primary key
     * @param int $lockMode
     * @param int $lockVersion
     * @return object
     */
    public function find($entityName, $identifier, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->getRepository($entityName)->find($identifier, $lockMode, $lockVersion);
    }

    /**
     * Tells the EntityManager to make an instance managed and persistent.
     *
     * The entity will be entered into the database at or before transaction
     * commit or as a result of the flush operation.
     *
     * NOTE: The persist operation always considers entities that are not yet known to
     * this EntityManager as NEW. Do not pass detached entities to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     */
    public function persist($entity)
    {
        if ( ! is_object($entity)) {
            throw new \InvalidArgumentException(gettype($entity));
        }
        $this->_errorIfClosed();
        $this->_unitOfWork->persist($entity);
    }

    /**
     * Removes an entity instance.
     *
     * A removed entity will be removed from the database at or before transaction commit
     * or as a result of the flush operation.
     *
     * @param object $entity The entity instance to remove.
     */
    public function remove($entity)
    {
        if ( ! is_object($entity)) {
            throw new \InvalidArgumentException(gettype($entity));
        }
        $this->_errorIfClosed();
        $this->_unitOfWork->remove($entity);
    }

    /**
     * Refreshes the persistent state of an entity from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $entity The entity to refresh.
     */
    public function refresh($entity)
    {
        if ( ! is_object($entity)) {
            throw new \InvalidArgumentException(gettype($entity));
        }
        $this->_errorIfClosed();
        $this->_unitOfWork->refresh($entity);
    }

    /**
     * Determines whether an entity instance is managed in this EntityManager.
     *
     * @param object $entity
     * @return boolean TRUE if this EntityManager currently manages the given entity, FALSE otherwise.
     */
    public function contains($entity)
    {
        return $this->_unitOfWork->isScheduledForInsert($entity) ||
            $this->_unitOfWork->isInIdentityMap($entity) &&
            ! $this->_unitOfWork->isScheduledForDelete($entity);
    }


    /**
     * Factory method to create EntityManager instances.
     *
     * @param mixed $conn An array with the connection parameters or an existing
     *      Connection instance.
     * @param Configuration $config The Configuration instance to use.
     * @param EventManager $eventManager The EventManager instance to use.
     * @return EntityManager The created EntityManager.
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null);
}