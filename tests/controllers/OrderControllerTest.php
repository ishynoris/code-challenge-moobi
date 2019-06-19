<?php 

require_once "src/models/Order.php";
require_once "src/controllers/OrderController.php";
require_once "src/controllers/ProductController.php";

use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

class OrderControllerTest extends TestCase {

    use TestCaseTrait;

    private $controller = null, $prodController = null;
    private $conn = null;
    private static $pdo = null;

    public function getConnection() {

        $this->controller = is_null($this->controller) ? new OrderController : $this->controller;
        $this->prodController = is_null($this->prodController) ? new ProductController : $this->prodController;

        try {
            $host = DB_HOST;
            $dbName = DBConnection::get_db_name();
            $username = DB_USER;
            $password = DB_PASSWORD;

            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
            );
            $strConnection = "mysql:host=$host;dbname=$dbName";

            if (is_null($this->conn)) {
                if (is_null(self::$pdo)) {
                    self::$pdo = new PDO($strConnection, $username, $password, $options);
                    self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, $dbName);
            }
            return $this->conn;

        } catch (PDOException $e) {
            echo "Falha: " . $e->getMessage() . "\n";
        }
        return null;
    }
 
    protected function getDataSet() {
        return $this->createFlatXmlDataSet('./tests/persistence/order_db.xml');
    }


    public function testRowsCount() {
        $expectedTable = $this->createFlatXmlDataSet("./tests/persistence/order_db.xml")
                ->getTable("tb_order");
        $rows = $expectedTable->getRowCount();
        $this->assertEquals(5, $rows);
    }

    public function testAddNewOrder() {
        $values = [
            Order::VALUE => 2500,
            Order::DISCOUNT => 125,
            Order::NUM_PARCEL => 1,
            Order::VALUE_PARCEL => 2500,
            Order::PAYMENT => "Bank Slip",
            Order::COD_PAYMENT => 2,
            Order::STATUS => "Open",
            Order::COD_STATUS => 2,
            Order::DATE => ValuesUtil::format_date(),
            Order::DATE_UPDATE => ValuesUtil::format_date(),
        ];
        $order = new Order;
        $order->from_values($values);
        $id = $this->controller->insert($order);
        $this->assertGreaterThan(0, $id);
    }

    public function testAddItensOrder() {
        $idOrder = 6;
        $itens = [
            $this->controller->get_item_by_id_product(2),
            $this->controller->get_item_by_id_product(3),
        ];

        $hasProductWithoutStock = false;

        $products = array_map(function($item) {
            return $this->prodController->get_product($item->idProduct);
        }, $itens);

        foreach ($products as $prod) {
            if ($prod->stock <= 0) {
                $hasProductWithoutStock = true;
                break;
            }
        }
        $id = boolval($hasProductWithoutStock) 
                ? -1 : $this->controller->insert_itens($idOrder, $itens);
        $this->assertGreaterThan(0, $id);
    }
}