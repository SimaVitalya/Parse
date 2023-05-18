<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ParseRozetkaController extends Controller
{
    public function index()
    {
        $client = new Client();

        $request = $client->get('https://xl-catalog-api.rozetka.com.ua/v4/goods/get?front-type=xl&country=UA&lang=ru&sort=expensive&category_id=80004');
        //        &page=3 если нужно увижеть страницу выше это мы ищем по категоии тут мы выбераем категорию


        $response = json_decode($request->getBody()->getContents(), true);
        //тут мы преобразуем наши данные через json... а ниже мы их переводим из массива в строку что бы подставить ее позже в нашой переменной requesItems
        $myIdItems = (implode(',', $response['data']['ids']));


        //ниже мы выводим все наши продукты в данной категории
        $requestItems = $client->get("https://xl-catalog-api.rozetka.com.ua/v4/goods/getDetails?front-type=xl&country=UA&lang=ru&with_groups=1&with_docket=1&goods_group_href=1&product_ids=$myIdItems");
        $responseItems = json_decode($requestItems->getBody()->getContents(), true);

        //Тут находиться характеристики   товара
        //      dd($responseItems);
//        foreach ($responseItems['data'] as $item) {
//            $docket = $item['docket'];
//            if (!empty($docket)) {
//                dd($docket) ;
//            }
//        }


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
//            $description = $item['description'];
            $price = $item['price'];
            $image_url = $item['image_main'];


            $title = mysqli_real_escape_string($conn, $title);
//            $description = mysqli_real_escape_string($conn, $description);
            $image_url = mysqli_real_escape_string($conn, $image_url);


            $sql = "INSERT INTO products (title, price, image_url) VALUES ('$title', '$price', '$image_url')";


            if ($conn->query($sql) === TRUE) {
                echo "Product added successfully. <br>";
            } else {
                echo "Error adding product: " . $conn->error;
            }

            // добавили что бы не забанили
            sleep(30);
        }

        //закрываем соединение с базой данных
        mysqli_close($conn);
  }
}
