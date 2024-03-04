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
                                {$data->bar_code},'{$data->date->register}','{$data->origin}')";
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


require_once 'class/Product.php';
require_once 'class/ProductGateway.php';

try {
    $ini = parse_ini_file('config/config.ini');
    $dbname = $ini['name'];

    $conn = new PDO('sqlite:'.$dbname);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    Product::setConnection($conn);
    $product = new Product;

} catch(Exception $e) {
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