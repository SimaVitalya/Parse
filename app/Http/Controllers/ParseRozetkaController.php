<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ParseRozetkaController extends Controller
{
    public function index()
    {
        $client = new Client();

        $request = $client->get("https://xl-catalog-api.rozetka.com.ua/v4/goods/get?front-type=xl&country=UA&lang=ru&sort=expensive&category_id=80003&page=1&per_page=60");

        $response = json_decode($request->getBody()->getContents(), true);

        $total_pages = $response['data']['total_pages']; // общее количество страниц

        $per_page = $response['data']['goods_limit']; // количество записей на странице
//        dd($total_pages, $per_page);

        for ($page = 1; $page <= $total_pages; $page++) {
            $request = $client->get("https://xl-catalog-api.rozetka.com.ua/v4/goods/get?front-type=xl&country=UA&lang=ru&sort=expensive&category_id=80003&page=$page&per_page=$per_page");

            $response = json_decode($request->getBody()->getContents(), true);

            $myIdItems = (implode(',', $response['data']['ids']));

            //ниже мы выводим все наши продукты в данной категории
            $requestItems = $client->get("https://xl-catalog-api.rozetka.com.ua/v4/goods/getDetails?front-type=xl&country=UA&lang=ru&with_groups=1&with_docket=1&goods_group_href=1&product_ids=$myIdItems");
            $responseItems = json_decode($requestItems->getBody()->getContents(), true);

            //Тут находиться характеристики   товара

            //Подключаемся к нашей базе данных
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "rozetkaparse";
            // устанавливаем соединение с базой данных
            $conn = mysqli_connect($servername, $username, $password, $dbname);

            // проверяем соединение
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            //тут должен быть форич
            foreach ($responseItems['data'] as $item) {
                $title = $item['title'];
                $price = $item['price'];
                $image_url = $item['image_main'];

                $title = mysqli_real_escape_string($conn, $title);
                $image_url = mysqli_real_escape_string($conn, $image_url);

                $sql = "INSERT INTO products (title, price, image_url) VALUES ('$title', '$price', '$image_url')";

                if ($conn->query($sql) === true) {
                    echo "Product added successfully. <br>";
                } else {
                    echo "Error adding product: " . $conn->error;
                }

                // добавили что бы не забанили
                sleep(30);
            }

            //закрываем соединение с базой данных
            mysqli_close($conn);

            // задержка в 20 секунд перед следующей итерации цикла
            sleep(20);
        }
    }
}
