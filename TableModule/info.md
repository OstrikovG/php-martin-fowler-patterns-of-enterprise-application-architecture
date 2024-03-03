# Practical PHP Patterns: Table Module
https://dzone.com/articles/practical-php-patterns/practical-php-patterns-table

The Table Module pattern is implemented by coding a class that encapsulates all the interaction with a database table. The usage of such a class may be singleton-like or more instances can be created to perform operations on subsets of the original table.

Given that the relationship between Table Modules and tables is biunivocal, often different Table Modules can be coordinated from client code or from an upper layer. This pattern can also be applied to views, or queries, since both are virtual tables and produce a record set which can be passed to the Table Module.

## Modelling phase

In this modelization there are no single objects for the rows of the table, like in other patterns such as Active Record or Row Data Gateway. The Table Module encapsulates all the business logic in a database-centric approach, and its method signatures use primitive values for parameters and return type, or at most data containers such as Value Objects.

The Table Module composes a record set, or a database connection. However when using a connection object it is less versatile (queries to produce virtual tables can't be outsourced to maintain orthogonality) and can cross the boundary of another pattern, the Table Data Gateway. The more the dependencies are reduced, the more the table data can be filtered or altered before the Table Module receives the set of rows, enhancing the reusability of the class.

## Pros and cons

A Table Module ties the client code to the entities present in a relational database (although this is true in some way for many patterns oriented to database interaction.) It is however a pattern that strives to keep data with its related behavior and wraps external infrastructure like databases in something that can be substituted in testing or in other environments. Still more clever than scattering queries in many different php scripts.

Ideally SQL is not needed to use a Table Module from the client point of view: it is either outsourced into other classes that produce a record set which the Table Module examines, or encapsulated by the Table Module itself; this pattern is a simple form of Repository, that still conforms to the data structures defined by the database. The primary difference is the absence of a basic entity class, so we can't talk about an access to an in-memory collection of objects; though, it is a stronger form of encapsulation in certain cases, because an entity class for the single row may not be needed nor have any logic.

This pattern is also much simpler than the Domain Model one: there is no translation of data between objects and rows. The lack of mapping can be a disadvantage - coupling to the schema - but also an advantage in implementation speed, particularly if you want to interface with a database for interoperability. Not all web applications need a Domain Model: some are glue code, some are interfaces to other software systems. In practice, the time spent to implement a complex model and its mapping can be prohibitive for a throw-away application or even for a small or medium sized project that does not pass the complexity threshold.

Beware that if you already encapsulate large computations on the data, you may want to push the related logic in the database for better performance, defining it with SQL queries. This is done with a different pattern, the Table Data Gateway, but can be more difficult and heavier to test effectively in automated test suites. Essentially if you use SQL you're tied to create a database and a connection whenever you want to test your business logic, and even if you pass a PDO object or another abstraction to the table module, a lightweight (read easier to employ in tests) database such as Sqlite may not support all the features you use in your SQL queries. Add that this process should be done for every single test to promote their isolation, and you get the picture.

## Samples

It makes sense to implement this pattern when the table data as a whole is more significant than a single row. Thus, the table may not contain entities but it's likely to store values or associating entities. In fact, the sample code deals with an example where the dataset is treated as a whole: a table containing analytics data, such as the browser and resolution of uses that have visited a website.

    <?php
    /**
     * Provide statistics on the unique visitors data. 
     * Implementation of a Table Module.
     */
    class StatisticsModule
    {
        /**
         * These rows can be the result of any query that conforms to the 
         * schema defined by this Table Module. The real table may not even
         * exist, being substituted by views or a set of queries.
         */
        public function __construct(array $rows)
        {
            $this->_rows = $rows;
        }
    
        public function getMostPopularBrowser()
        {
            $browsers = array();
            foreach ($this->_rows as $row) {
                if (!isset($browsers[$row['browser']])) {
                    $browsers[$row['browser']] = 0;
                }
                $browsers[$row['browser']]++;
            }
            arsort($browsers);
            reset($browsers);
            return current(array_keys($browsers));
        }
    
        /**
         * @param float $margin minimum percentual for considering a
         *                      resolution used by visitors
         */
        public function isResolutionUsed($resolution, $margin = 0.1)
        {
            $visitors = 0;
            foreach ($this->_rows as $row) {
                if ($row['resolution'] == $resolution) {
                    $visitors++;
                }
            }
            return $visitors / count($this->_rows) > $margin;
        }
    }
    
    function create_row($browser, $resolution, $page)
    {
        return array(
            'browser' => $browser,
            'resolution' => $resolution,
            'page' => $page
        );
    }
    
    // array is used for simplicity of stubbing here
    // a RecordSet implementation will be more performant
    $recordSet = array(
        create_row('MSIE', '1024x768', '/'),
        create_row('MSIE', '640x480', '/members'),
        create_row('MSIE', '1024x768', '/'),
        create_row('Firefox', '1280x1024', '/'),
        create_row('MSIE', '1024x768', '/'),
        create_row('Firefox', '1024x768', '/'),
        create_row('Firefox', '1024x768', '/'),
        create_row('Firefox', '1300x768', '/members'),
        create_row('Safari', '800x600', '/'),
        create_row('MSIE', '1024x768', '/members'),
        create_row('MSIE', '1024x768', '/members'),
        create_row('Chrome', '1024x768', '/members'),
        create_row('Chrome', '1280x1024', '/contacts'),
        create_row('Firefox', '1280x1024', '/'),
        create_row('MSIE', '124x768', '/about'),
    );
    
    // client code
    $statisticsModule = new StatisticsModule($recordSet);
    echo $statisticsModule->getMostPopularBrowser(), "\n";
    var_dump($statisticsModule->isResolutionUsed('1024x768'));
    var_dump($statisticsModule->isResolutionUsed('640x480'));
