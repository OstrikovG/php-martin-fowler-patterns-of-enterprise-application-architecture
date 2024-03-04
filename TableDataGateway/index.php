<?php

class UserGateway {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll() {
        $stmt = $this->db->prepare('SELECT * FROM users');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data) {
        $stmt = $this->db->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
        $stmt->execute(['name' => $data['name'], 'email' => $data['email']]);
        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute(['id' => $id, 'name' => $data['name'], 'email' => $data['email']]);
        return $stmt->rowCount();
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}

$db = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');
$userGateway = new UserGateway($db);

$user = $userGateway->findById(1);
echo $user['name'] . ' ' . $user['email'] . "n";

$users = $userGateway->findAll();
foreach ($users as $user) {
    echo $user['name'] . ' ' . $user['email'] . "n";
}

$id = $userGateway->insert(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
echo 'New user id: ' . $id . "n";

$userGateway->update($id, ['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);

$userGateway->delete($id);
