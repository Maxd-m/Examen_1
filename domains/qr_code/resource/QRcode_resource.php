<?php

require_once '../QRcode.php';

class QRCodeResource{
    private $qrCode;

    public function __construct()
    {
        $this->qrCode = new QRcode();

    }

    // POST /api/v1/products
    public function store()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->name) && !empty($data->sku) && !empty($data->description) && !empty($data->price) && !empty($data->stock)) {
            $this->product->name = $data->name;
            $this->product->sku = $data->sku;
            $this->product->description = $data->description;
            $this->product->price = $data->price;
            $this->product->stock = $data->stock;


            if ($this->product->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Producto creado exitosamente",
                    "id" => $this->product->id
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el Producto"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos"));
        }
    }


}