<?php
require_once "controllers/Controller.php";
require_once "models/Product.php";
require_once "persistence/OrderPersistence.php";
require_once "persistence/ProductPersistence.php";

class OrderController extends Controller {

    private $persistence, $productPersistence;

    function __construct() {
        parent::__construct("btn-remove", "btn-quantity", "btn-add-cart", "btn-search", "btn-confirm", "btn-back");
        
        $this->persistence = new OrderPersistence;
        $this->productPersistence = new ProductPersistence;
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function post(string $input): int {

        switch($input) {
            case "btn-remove": return $this->remove($input);
            case "btn-quantity": return $this->quantity($input);
            case "btn-add-cart": return $this->add_cart($input);
            case "btn-back": return $this->back();
            case "btn-confirm": return $this->confirm();
            case "btn-finish": return $this->finish();
        }
        return OK;
    }

    public function products(): array {
        $searchable = isset($_POST['searchable']) ? $_POST['searchable'] : "";
        if (isset($_POST['btn-search']) && strlen($searchable) > 0) {
            return $this->productPersistence->select_by_search($searchable);
        }
        return $this->productPersistence->select_all_active();
    }

    public function order_itens(): stdClass {
        $itens = new stdClass;
        $itens->products = $_SESSION['cart'];
        $itens->titles = [
            [ "width" => 5, "text" => "Code" ],
            [ "width" => 40, "text" => "Name" ],
            [ "width" => 10, "text" => "Price" ],
            [ "width" => 20, "text" => "Barcode" ],
            [ "width" => 15, "text" => "Quantity" ],
            [ "width" => 10, "text" => "Remove" ],
        ];
        return $itens;
    }

    private function quantity(string $input): int {
        $id = intval($_POST[$input]);
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        if (array_key_exists($id, $cart)) {
            $cart[$id]['quantity'] = intval($_POST['quantitys'][$id]);
            $_SESSION['cart'] = $cart;
        }
        return 0;
    }

    private function remove(string $input): int {
        $id = intval($_POST[$input]);
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        if (array_key_exists($id, $cart)) {
            unset($cart[$id]);
            $_SESSION['cart'] = $cart;
        }
        return 0;
    }

    private function add_cart(string $input): int {
        $id = intval($_POST[$input]);
        $cart = $_SESSION['cart'];
        if (!isset($cart[$id])) {
            $product = $this->productPersistence->select_by_id($id);
            $cart[$id] = [
                "item" => $product,
                "quantity" => 1,
            ];
            $_SESSION['cart'] = $cart;
        };
        return 0;
    }

    private function back(): int {
        return $this->go_to(Controller::base_url("pedido/novo"));
    }

    private function confirm(): int {
        return $this->go_to(Controller::base_url("pedido/finalizar"));
    }

    private function go_to(string $url): int {
        header("Location: $url");
        return 0;
    }
}
