<?php

require_once "src/persistence/DBConnection.php";
require_once "src/models/Product.php";

class ProductPersistence {

    const REPLACE_WHERE = "WHERE false ";
    const REPLACE_ORDER_BY = "ORDER BY P.id ";

    const SELECT = "SELECT P.id, P.name, P.price, P.description, P.barcode, " 
                . "P.cod_status, P.date, P.date_update, P.stock "
                . "FROM tb_product P "
                . self::REPLACE_WHERE
                . self::REPLACE_ORDER_BY;

    const INSERT = "INSERT INTO tb_product (name, price, stock, description, barcode, cod_status, date, date_update) "
                . "VALUES (:fname, :fprice, :fstock, :fdescription, :fbarcode, :fcod_status, :fdate, :fdate_update); ";

    const UPDATE = "UPDATE tb_product set name = :fname, price = :fprice, stock = :fstock, description = :fdescription, " 
                . "barcode = :fbarcode, cod_status = :fcod_status, date_update = :fdate_update "
                . self::REPLACE_WHERE;

    const DELETE = "DELETE FROM tb_product "
                . self::REPLACE_WHERE;

    public function select_by_id(int $id): Product {
        $args = [ 
            ":" . Product::ID => $id,
        ];
        $where = "WHERE P.id = :id ";
        $products = $this->execut_select($where, $args);
        return sizeof($products) > 0 ? array_pop($products) : new Product;
    }

    public function select_by_order(int $idOrder): array {
        $args = [ 
            ":fid_order" => $idOrder,
        ];
        $where = "WHERE P.id in (SELECT OI.id_product FROM tb_order_itens OI WHERE OI.id_order = :fid_order) ";
        return $this->execut_select($where, $args);
    }

    public function select_by_search(string $search): array {
        $search = "%$search%";
        $args = [
            ":f" . Product::COD_STATUS => PRO_ACTIVE,
            ":f" . Product::NAME => $search,
            ":f" . Product::BARCODE => $search,
            ":f" . Product::DESCRIPTION => $search,
            ":f" . Product::PRICE => $search,
        ];
        $where = "WHERE P.cod_status = :fcod_status AND ("
                . "P.name like :fname "
                . "OR P.barcode like :fbarcode "
                . "OR P.description like :fdescription "
                . "OR P.price like :fprice"
                . ") ";
        return $this->execut_select($where, $args);
    }

    public function select_all(): array {
        $args = [ 
            ":" . Product::ID => 0 
        ];
        $where = "WHERE P.id > :id ";
        return $this->execut_select($where, $args);
    }

    public function select_all_active(): array {
        $args = [ 
            ":" . Product::COD_STATUS => PRO_ACTIVE
        ];
        $where = "WHERE P.cod_status = :cod_status ";
        return $this->execut_select($where, $args);
    }

    public function insert(Product $product): int {
        $query = self::INSERT;
        $values = $product->db_values();
        $db = new DBConnection;
        return $db->insert($query, $values);
    }

    public function update(Product $product): int {
        $where = "WHERE id = :fid ";
        $query = str_replace(self::REPLACE_WHERE, $where, self::UPDATE);
        $values = $product->db_values(true);
        if (isset($values[":f" . Product::DATE])) {
            unset($values[":f" . Product::DATE]);
        }
        
        $db = new DBConnection;
        return $db->update($query, $values);
    }

    public function delete(int $id): int {
        $where = "WHERE id = :id;";
        $query = str_replace(self::REPLACE_WHERE, $where, self::DELETE);
        $db = new DBConnection;
        return $db->delete($query, [ ":id" => $id ]);
    }

    private function execut_select(string $where = null, array $args = []): array {
        $query = is_null($where) || sizeof($args) === 0 
                ? self::SELECT_ALL
                : str_replace(self::REPLACE_WHERE, $where, self::SELECT);
        $db = new DBConnection;
        $values = $db->select($query, $args);

        return array_map(function($v) {
            $product = new Product;
            $product->from_values($v);
            return $product;
        }, $values);
    }
}