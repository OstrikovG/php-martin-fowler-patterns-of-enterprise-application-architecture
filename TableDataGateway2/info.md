# PHP Design Patterns: Table Data Gateway
https://dev.to/xxzeroxx/php-design-patterns-table-data-gateway-43jj

## What would be Table Data Gateway?

Table Data Gateway is a design pattern in which it is represented by a class that transports data between the application and the database. Thus, classes have only persistence methods as operations, that is, data recording.
![img.png](img%2Fimg.png)

We will also have another class, which is the application's business class, using the gateway class whenever it needs to search or save data in the database.
![img_1.png](img%2Fimg_1.png)

In the majority of cases, a Table Data Gateway deals with a relational model, having a 1:1 relationship with the main tables of the database.
![img_2.png](img%2Fimg_2.png)

## Example

**Step 1 - Directory System:**

📦Table_Data_Gateway
┣ 📂class
┃ ┣ 📜Product.php
┃ ┗ 📜ProductGateway.php
┣ 📂config
┃ ┗ 📜config.ini
┣ 📂database
┃ ┗ 📜product.db
┗ 📜index.php

**Step 2 - Database Config File:**

    host =
    name = database/product.db
    user =
    pass =
    type = sqlite

**Step 3 - Database:**

    CREATE TABLE product(
        id INTEGER PRIMARY KEY NOT NULL,
        description TEXT,
        stock FLOAT,
        cost_price FLOAT,
        sale_price FLOAT,
        bar_code TEXT,
        date_register DATE,
        origin CHAR(1)
    );

**Step 4 - ProductGateway Class:**

    <?php
    
        class ProductGateway
        {
    
            private static $conn;
    
            public function __construct()
            {
    
            }
    
            public static function setConnection(PDO $conn)
            {
                self::$conn = $conn;
            }
    
            public function find($id, $class = 'stdClass')
            {
                $sql = "SELECT * FROM product WHERE id = '$id'";
                print "$sql <br>";
                $result = self::$conn->query($sql);
                return $result->fetchObject($class);
            }
    
            public function all($filter = '', $class = 'stdClass')
            {
                $sql = "SELECT * FROM product";
    
                if( $filter )
                {
                    $sql .= " WHERE $filter";
                }
    
                print "$sql <br>";
                $result = self::$conn->query($sql);
                return $result->fetchAll(PDO::FETCH_CLASS, $class);
    
            }
    
            public function delete($id)
            {
                $sql = "DELETE FROM product WHERE id = '$id'";
                print "$sql <br>";
                return self::$conn->query($sql);
            }
    
            public function save($data)
            {
    
                if( empty($data->id) )
                {
                    $sql = "INSERT INTO product
                                    (description, stock, cost_price, sale_price, bar_code, date_register, origin)
                                    VALUES
                                    ('{$data->description}',{$data->stock},{$data->cost_price},{$data->sale_price},
                                        {$data->bar_code},'{$data->date-register}','{$data->origin}')";
                }
                else
                {
                    $sql = "UPDATE product SET 
                                    description = '{$data->description}', stock = '{$data->stock}', cost_price = '{$data->cost_price}',
                                    sale_price = '{$data->sale_price}', bar_code = '{$data->bar_code}', date_register = '{$data->date_register}',
                                    origin = '{$data->origin}'
                                    WHERE id = '{$data->id}'";
                }
    
                print "$sql <br>";
                return self::$conn->exec($sql);
    
            }
    
        }

**Step 5 - Product Class:**

    <?php
    
        class Product
        {
    
            private $data;
    
            public function __construct()
            {
    
            }
    
            public static function setConnection( PDO $conn)
            {
                ProductGateway::setConnection($conn);
            }
    
            public function __get($prop)
            {
                return $this->data[$prop];
            }
    
            public function __set($prop, $value)
            {
                $this->data[$prop] = $value;
            }
    
            public static function find($id)
            {
                $gw = new ProductGateway;
              return $gw->find($id, 'Product');
            }
    
            public static function all($filter = '')
            {
                $gw = new ProductGateway;
              return $gw->all($filter, 'Product');
            }
    
            public function save()
            {
                $gw = new ProductGateway;
                return $gw->save( (object) $this->data);
            }
    
            public function delete()
            {
                $gw =  new ProductGateway;
                return $gw->delete($this->id);
            }
    
            public function getProfitMargin()
            {
                return (($this->sale_price - $this->cost_price)/$this->cost_price)*100;
            }
    
            public function registerPurchase($cost, $quantity)
            {
                $this->cost_price = $cost;
                $this->stock += $quantity;
            }
    
        }

## Testing

    <?php
    
        require_once 'class/Product.php';
        require_once 'class/ProductGateway.php';
    
        try
        {
            $ini = parse_ini_file('config/config.ini');
            $dbname = $ini['name'];
    
            $conn = new PDO('sqlite:'.$dbname);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            Product::setConnection($conn);
            $product = new Product;
    
        }
        catch(Exception $e)
        {
            print $e->getMessage();
        }

## Recording a product in the database:

    $product->description = 'Juice';
    $product->stock = 8;
    $product->cost_price = 12;
    $product->sale_price = 18;
    $product->bar_code = '123123123';
    $product->origin = 'S';
    $product->date_register = date('Y-m-d');
    $product->save();

## Update a product from the database:

    $update = $product::find(1);
    $update->description = "Grape Juice";
    $update->save($product); 

## List Products:

    foreach( $product::all() as $p )
    {
        print $p->description . ' ';
        print $p->cost_price . ' ';
        print $p->sale_price . "<br>";
    }

## Business Methods:

    $p = $product::find(1);
    $p->registerPurchase(24,2); //(cost,quantity)
    $p->save($product);
    
    print $p->getProfitMargin();