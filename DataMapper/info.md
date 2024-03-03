# Data Mapper (средство отображения данных)
https://rozanov-lev.ru/shablony/data-mapper-sredstvo-otobrazheniya-dannyh/

## Проблема

Объекты не организованы как таблицы в реляционной базе данных. Как известно, таблицы реляционной базы данных —это сеточные структуры, состоящие из строк и столбцов. Одна строка может связываться с другой строкой в другой (или даже в той же) таблице с помощью внешнего ключа.

С другой стороны, объекты обычно связаны один с другим более естественным образом. Один объект может содержать ссылку на другой, а в различных структурах данных одни и те же объекты могут быть организованы разными способами.

Их можно скомбинировать по-разному, и сформировать новые связи между ними можно прямо во время выполнения программы.

Реляционные базы данных являются оптимальными для работы с большими объемами табличных данных, в то время как классы и объекты инкапсулируют небольшие специализированные фрагменты информации.

Такое отличие классов от реляционных баз данных нередко называется объектно-реляционной потерей соответствия (или просто потерей соответствия).

Как же сделать такой переход, чтобы преодолеть подобное отличие? В качестве одного варианта можно создать класс (или ряд классов), отвечающий за решение только этой задачи, фактически скрыв базу данных от модели предметной области и сгладив неизбежные острые углы такого перехода.

## Решение

Можно создать один класс Mapper, предназначенный для обслуживания нескольких объектов. Но, как правило, в прикладном коде приходится наблюдать отдельный класс Mapper, созданный в качестве главного для модели предметной области в соответствии с шаблоном Domain Model.

    abstract class Mapper
    {
    protected \PDO $pdo;
    
        public function __construct()
        {
            $reg = Registry::instance();
            $this->pdo = $reg->getPdo();
        }
    
        public function find(int $id): ? DomainObject
        {
            $this->selectstmt()->execute([$id]);
            $row = $this->selectstmt()->fetch();
            $this->selectstmt()->closeCursor();
    
            if (!in_array($row)) {
                return null;
            }
    
            if (!isset($row['id'])) {
                return null;
            }
    
            $object = $this->createObject($row);
            return $object;
        }
    
        public function createObject(array $raw): DomainObject
        {
            $obj = $this->doCreateObject($raw);
            return $obj;
        }
    
        public function insert(DomainObject $obj): void
        {
            $this->doInsert($obj);
        }
    
        abstract public function update(DomainObject $obj): void;
        abstract protected function doCreateObject(array $raw): DomainObject;
        abstract protected function doInsert(DomainObject $object): void;
        abstract protected function selectStmt(): \PDOStatement;
        abstract protected function targetClass(): string;
    }

**А теперь пример дочернего класса:**
    
    class VenueMapper extends Mapper
    {
    private \PDOStatement $selectStmt;
    private \PDOStatement $updateStmt;
    private \PDOStatement $insertStmt;
    
        public function __construct()
        {
            parent::__construct();
            $this->selectStmt = $this->pdo->prepare("SELECT * FROM venue WHERE id = ?");
            $this->updateStmt = $this->pdo->prepare("UPDATE venue SET name = ?, id = ? WHERE id = ?");
            $this->insertStmt = $this->pdo->prepare("INSERT INTO venue (name) VALUES (?)");
        }
    
        protected function targetClass(): string
        {
            return Venue::class;
        }
    
        public function getCollection(array $raw): VenueCollection
        {
            return new VenueCollection($raw, $this);
        }
    
        protected function doCreateObject(array $raw): Venue
        {
            $obj = new Venue((int)$raw['id'], $raw['name']);
            return $obj;
        }
    
        protected function doInsert(DomainObject $obj): void
        {
            $values = [$obj->getName()];
            $this->insertStmt->execute($values);
            $id = $this->pdo->lastInsertId();
            $obj->setId((int)$id);
        }
    
        public function update(DomainObject $obj): void
        {
            $values = [
                $obj->getName(),
                $obj->getId(),
                $obj->getId()
            ];
            $this->updateStmt->execute($values);
        }
    
        public function selectStmt(): \PDOStatement
        {
            return $this->selectStmt;
        }
    }
**Пример использования:**
    
    $mapper = new VenueMapper();
    
    // добавляем объект в базу данных
    $venue = new Venue(-1, "Лучший бар");
    $mapper->insert($venue);
    
    // ищем объект
    $venue = $mapper->find($venue->getId());
    
    // изменяем объект
    $venue->setName('Самый лучший бар');
    $mapper->update($venue);
    
    // получаем объект для проверки
    $venue = $mapper->find($venue->getId());

## UML

![img.png](img%2Fimg.png)

_UML-диаграмма шаблона Data Mapper (средство отображения данных)_
