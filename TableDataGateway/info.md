# Шаблон Table Data Gateway с примерами на PHP
https://danyloff.tech/2023/04/25/%D1%88%D0%B0%D0%B1%D0%BB%D0%BE%D0%BD-table-data-gateway/

Table Data Gateway – это паттерн проектирования, который используется для управления доступом к данным в базе данных. Он представляет собой объект, который обеспечивает доступ к таблице в базе данных и предоставляет методы для выполнения операций CRUD (Create, Read, Update, Delete) над данными.

Основная идея Table Data Gateway заключается в том, что каждая таблица в базе данных имеет свой собственный объект-шлюз, который отвечает за доступ к данным этой таблицы. Это позволяет разделить логику работы с базой данных на отдельные объекты и упростить ее поддержку и расширение.

**Пример реализации Table Data Gateway на PHP**

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
В этом примере мы создали класс UserGateway, который представляет собой шлюз для таблицы users в базе данных. Конструктор класса принимает объект PDO, который используется для выполнения запросов к базе данных.

Метод findById() выполняет запрос к базе данных для поиска пользователя по его идентификатору. Метод findAll() возвращает все записи из таблицы users. Метод insert() добавляет новую запись в таблицу users и возвращает идентификатор новой записи. Метод update() обновляет запись в таблице users по ее идентификатору. Метод delete() удаляет запись из таблицы users по ее идентификатору.

**Пример использования класса UserGateway**

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

В этом примере мы создали объект PDO для подключения к базе данных и передали его в конструктор класса UserGateway. Затем мы использовали методы класса UserGateway для выполнения операций CRUD над данными в таблице users.

## Заключение

Table Data Gateway – это простой и эффективный паттерн проектирования для работы с базами данных. Он позволяет разделить логику работы с базой данных на отдельные объекты и упростить ее поддержку и расширение.