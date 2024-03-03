# Domain Model (модель предметной области)

## Проблема

При использовании шаблона Transaction Script дублирование становится настоящей проблемой, когда в различных сценариях приходится выполнять одни и те же задачи. Можно попробовать вынести повторяющие задачи в отдельные модули, но в перспективе это все равно приводит к копи-пасту.

## Решение

Для применения шаблона Domain Model, нужно отделять модели от представления, а сами модели должны являться классами, которые напрямую отражают таблицы в базе данных.

Продолжим работу с систему приведенную ранее: с заведениями (venue), места (space) и события (event).

**Определяем абстрактный класс DomainObject:**

    abstract class DomainObject
    {
    public function __construct(private int $id)
    {
    }
    
        public function getId(): int
        {
            return $this->id;
        }
        
        public static function getCollection(string $type): Collection
        {
            return Collection::getCollection($type);
        }
    }
**Класс заведений (venue):**

    class Venue extends DomainObject
    {
    private SpaceCollection $spaces;
    
        public function __construct(int $id, private string $name)
        {
            $this->name = $name;
            $this->spaces = self::getCollection(Space::class);
            parent::__construct($id);
        }
        
        public function setSpaces(SpaceCollection $spaces): void
        {
            $this->spaces = $spaces;
        }
        
        public function getSpaces(): SpaceCollection
        {
            return $this->spaces;
        }
        
        public function addSpace(Space $space): void
        {
            $this->spaces->add($space);
            $space->setVenue($this);
        }
        
        public function setName(string $name): void
        {
            $this->name = $name;
        }
        
        public function getName(): string
        {
            return $this->name;
        }
    }
Остальные класс реализуются схожим образом и наследуют DomainObject.

## UML

![img.png](img%2Fimg.png)

_UML-диаграмма шаблона Domain Model (модель предметной области)_

## Применение

Несмотря на кажущуюся простоту, при некоторых бизнес-процессах — проектирование системы на основании шаблона Domain Model становится очень и очень трудной задачей. И несмотря на столь заманчивое разделение моделей от представления, перед проектированием системы следует провести тщательный анализ насчет эффективности применения данного шаблона.