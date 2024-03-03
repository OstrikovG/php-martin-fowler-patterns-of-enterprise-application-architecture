# Practical PHP Patterns: Data Mapper

https://dzone.com/articles/practical-php-patterns/practical-php-patterns-data

Data Mapper is one of the most advanced persistence-related patterns: an implementation of a Data Mapper stores objects (in general a whole object graph) in a database, and decouples the object model from the backend data representation, moving objects back and forth from the data store without introducing hardcode dependencies towards it.

The database back end used by most of the implementations are usually relational: Object-relational mappers are some of the widely used tools today (and are even fading in some areas where different types of database are preferred.)

## Dependencies

The interfaces for a Data Mapper can be put in the domain layer, but actual implementatons are in the category of infrastructure adapters and should be kept out of it, to promote the reuse and testing of domain layer classes without the need for a database back end or driver to be present.

When this pattern is employed in an application, there are no more dependencies from the domain layer to external components, and no subclassing like in the Active Record case. Domain entities and value objects become Plain Old PHP Objects which do not extend anything (extends keyword) and do not need to reflect any database schema (if they are saved in a relational db), ensuring the maximum freedom of modelling to the developers.

## Different kind of implementations

Early implementations of Data Mapper did not store an inner reference to a database connection or object that represent the link with the data store; in this case, result sets or some kind of raw data are passed to the Data Mapper, which reconstitutes the objects and encapsulates the process.

Currently it is preferred to put all the references to the database as internals of the Data Mapper implementation (or in an abstraction layer under it). Anyway the Data Mapper hides as much as possible, like the type of the database and related knowledge, from the client code (domain layer or an upper one).

The interface of the modern Data Mappers become from store() (insert and update) and remove() to one that comprehends also find() methods or a more complex system of querying; the implementation of querying is out of the scope of this pattern, but can be mixed up with it easily.

A distinction in the implementations of Data Mapper is in their scope. A Data Mapper can be specific to a particular Entity/Aggregate Root (single class or class with composed objects), or a generic implementation can be customized with metadata (annotations, XML configuration) to work with different classes. Generic implementations are usually very complex, and specific ones may become much more easy to code due to simplifications. However, generic Data Mappers are prone to reuse and present less bugs than the project-specific ones, which were the only alternative in the last years.

## Issues

The difficulties in implementing such a pattern are clear. Given a transaction, like an http request, the mapper has to keep track of the changed objects, and generate automatically the right DML queries to issue (SELECT, UPDATE, DELETE), in the right order and without leaving out any part of the modified data, avoid duplicating rows or update ones that do not exist anymore. This is a case of simple interface and complex implementation.

To avoid breaking encapsulation, implementations usually employ reflection to access private fields of the object to store or that the mapper is reconstituting. Other possible choices for the data access are specific constructors for reconstitution or specific interfaces for domain mapping, but this solution still breaks encapsulation by providing to the client code methods that are not meant to be called, or fields that should not even be seen out of the objects but are actually accessed. This results in a unclear Api which may promote dependencies on persistence-related items.

Providing metadata breaks encapsulation too, of course, but at least it is kept in the immediate so that it can change with the domain classes. Annotations are the preferred mean to specify metadata such as column names or relationships, and in PHP they are hidden in the docblock comments so that when the Data Mapper is not used they are just ignored.

Data Mapper does not provide a total illusion (abstraction) of an in-memory collection of objects: the knowledge that there is some kind of external data store scatters into the application upper layers. Moreover, eventually some particular issue of the storage leaks into the object part of the application. As an example, consider the performance of queries, which is often the object of discussion when using object-relational mappers. Usually not all the object graph is instantiated as it may be very large; tuning how large the instantiated part will be is a trade-off which depends on the underlying database. Furthermore, generated queries may result very inefficient to the point that much of the client code must hint the joins to perform via the Api.

## Examples

The generic Data Mapper Doctrine 2 (now in beta) is one of the few implementations in PHP of this pattern. As we've seen before in this article, specific implementations are dependent on the domain layer, so they are usually not reusable.

A working copy of Doctrine 2 would be too large in size for inclusion in this post, so we are only analyzing the interface that most of the client code would see: the Entity Manager (name borrowed from Hibernate and JPA, since Java application used Data Mappers for years before this pattern has seen adoption from PHP ones.)

The Entity Manager is not a domain specific interface, but other patterns like the Repository one can then compose the mapper to provid segregated interfaces for a particular class (aggregate root). As always, I have removed the less interesting methods or code to show the Api, and expanded the comments.

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