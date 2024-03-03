# Transaction Script (сценарий транзакция)
https://rozanov-lev.ru/shablony/transaction-script-sczenarij-tranzakcziya/

## Проблема

Каждый запрос должен каким-то образом обрабатываться. Во многих системах предусмотрен уровень, на котором анализируются и фильтруются входящие данные. Но в идеальном случае на этом уровне должны затем вызываться классы, предназначенные для выполнения запроса. Эти классы можно разделить по выполняемым функциям и обязанностям — возможно, с помощью интерфейса в соответствии с шаблоном Facade. Но такой подход требует серьезного и внимательного отношения к проектированию. Для некоторых проектов (обычно небольших по объему и срочных по характеру) такие издержки разработки могут оказаться неприемлемыми. В таком случае, возможно, придется встроить логику приложения в набор процедурных операций.

Каждая операция будет предназначена для обработки определенного запроса. Поэтому необходимо обеспечить быстрый и эффективный механизм достижения целей системы без потенциально дорогостоящих вложений в сложный проект.

Огромное преимущество данного шаблона заключается в скорости получения результатов. В каждом сценарии для достижения нужного результата обрабатываются входные данные и выполняются операции с базой данных. Помимо организации связанных вместе методов в одном классе и сохранения классов, построенных в соответствии с шаблоном Transaction Script, на их собственном уровне, как можно более независимом от уровней команд, управления и представления, для этого потребуется минимальное предварительное проектирование.

В то время как классы уровня логики приложения четко отделены от уровня представления, они в большей степени внедрены на уровне данных. Дело в том, что выборка и сохранение данных —это ключ к задачам, которые обычно решают подобные классы. Далее в этой главе будут пред- ставлены механизмы разделения объектов на уровнях логики приложения и базы данных. Но в классах, построенных в соответствии с шаблоном Transaction Script, обычно известно все о базе данных, хотя в них могут использоваться промежуточные классы для реализации подробностей обработки реальных запросов.

## Решение

Представим систему, в которой существуют заведения (venue), которые предоставляют места (space), для проведения событий (event). У каждого заведения может быть множество мест (например, у кинотеатра может быть несколько залов, а у театра, несколько сцен).

**Создадим таблицы базы данных:**

    CREATE TABLE venue (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name TEXT,
    PRIMARY KEY (id)
    )
    CREATE TABLE space (
    id INT(11) NOT NULL AUTO_INCREMENT,
    venue INT(11) DEFAULT NULL,
    name TEXT,
    PRIMARY KEY (id)
    )
    CREATE TABLE event (
    id INT(11) NOT NULL AUTO_INCREMENT,
    space INT(11) DEFAULT NULL,
    start MEDIUMTEXT,
    duration INT(11) DEFAULT NULL,
    name TEXT,
    PRIMARY KEY (id)
    )
**Определим абстрактный класс Base, который создает соединение с базой данных:**

    abstract class Base
    {
    private \PDO $pdo;
    private string $config = 'path/to/config.ini';
    
        public function  __construct()
        {
            $reg = Registry::instance();
            $options = parse_ini_file($this->config, true);
            $conf = new Conf($options['config']);
            $reg->setConf($conf);
            $dsn = $reg->getDSN();
            
            if (is_null($dsn)) {
                throw new Exception('DSN не определен');
            }
            
            $this->pdo = new \PDO($dsn);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        
        public function getPdo(): \PDO
        {
            return $this->pdo;
        }
    }
**Теперь создадим класс VenueManager, который будет работать с заведениями:**

    class VenueManager extends Base
    {
    private string $addVenue = 'INSERT INTO venue (name) VALUES (?)';
    private string $addSpace = 'INSERT INTO space (name, venue) VALUES (?, ?)';
    private string $addEvent = 'INSERT INTO event (name, space, start, duration) VALUES (?, ?, ?, ?)';
    
        // добавление заведения
        public function addVenue(string $name, array $spaces): array
        {
            $pdo = $this->getPdo();
            $ret = [];
            $ret['venue'] = [$name];
            $stmt = $pdo->prepare($this->addVenue);
            $stmt->execute($ret['venue']);
            $vid = $pdo->lastInsertId();
            $ret['spaces'] = [];
            $stmt = $pdo->prepare($this->addSpace);
            
            foreach ($spaces as $spaceName) {
                $values = [$spaceName, $vid];
                $stmt->execute($values);
                $sid = $pdo->lastInsertId();
                array_unshift($values, $sid);
                $ret['spaces'][] = $values;
            }
            
            return $ret;
        }
        
        // добавление события
        public function addEvent(int $spaceId, string $name, int $time, int $duration): void
        {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare($this->addEvent);
            $stmt->execute([$name, $spaceId, $time, $duration]);
        }
    }
## UML

![img.png](img%2Fimg.png)

_UML-диаграмма шаблона Transaction Script (сценарий транзакция)_

## Применение

Шаблон Transaction Script следует применять в небольших проектах, если есть уверенность, что они не перерастут в нечто большее. Такой подход не вполне допускает масштабирование проектируемой системы, потому что дублирование кода обычно начинает проявляться, когда сценарии неизбежно пересекаются. Безусловно, можно реорганизовать код, но полностью избавиться от его дублирования вряд ли удастся.