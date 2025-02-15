<?php 
require_once "src/controllers/ProductController.php";

$prodController = new ProductController;
$result = $prodController->post($prodController->input_name());
?>

<title>New Product</title>

<style>
    button {
        margin-right: 10px;
    }
</style>

<h2>New Product</h2>
<form method="post">
    <div class="form-field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Product name">
    </div>
    <div class="form-field">
        <label for="barcode">Barcode</label>
        <input id="barcode" name="barcode" placeholder="Barcode">
    </div>
    <div class="form-field">
        <label for="price">Price</label>
        <input type="number" id="price" name="price" placeholder="Unit price">
    </div>
    <div class="form-field">
        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" placeholder="Stock of product">
    </div>
    <div class="form-field">
        <label for="description">Description</label>
        <textarea name="description" id="description" cols="30" rows="4" placeholder="Some product's description"></textarea>
    </div>
    <div class="form-field">
        <label></label>
        <button type="submit" id="btn-save" name="btn-save">Save</button>
        <button type="submit" name="btn-back">Back</button> 
    </div>
</form>