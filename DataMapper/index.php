<?php

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