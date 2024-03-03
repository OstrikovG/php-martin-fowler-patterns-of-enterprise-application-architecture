<?php

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

class VenueManager extends Base
{
    private string $addVenue = 'INSERT INTO venue (name) VALUES (?)';
    private string $addSpace = 'INSERT INTO space (name, venue) VALUES (?, ?)';
    private string $addEvent = 'INSERT INTO event (name, space, start, duration) VALUES (?, ?, ?, ?)';

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

    public function addEvent(int $spaceId, string $name, int $time, int $duration): void
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare($this->addEvent);
        $stmt->execute([$name, $spaceId, $time, $duration]);
    }
}