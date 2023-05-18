<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ParseRozetkaController extends Controller
{
    public function index()
    {
        $client = new Client();

        $request = $client->get('https://xl-catalog-api.rozetka.com.ua/v4/goods/get?front-type=xl&country=UA&lang=ru&sort=expensive&category_id=80003');
        //        &page=3 если нужно увижеть страницу выше это мы ищем по категоии тут мы выбераем категорию

        $response= json_decode($request->getBody()->getContents(),true);
        //тут мы преобразуем наши данные через json... а ниже мы их переводим из массива в строку что бы подставить ее позже в нашой переменной requesItems
        $myIdItems = (implode(',',$response['data']['ids']) );


        $requestItems = $client->get("https://xl-catalog-api.rozetka.com.ua/v4/goods/getDetails?front-type=xl&country=UA&lang=ru&with_groups=1&with_docket=1&goods_group_href=1&product_ids=$myIdItems");
        $responseItems= json_decode($requestItems->getBody()->getContents(),true);
        dd($responseItems);






        //        $productTest = json_decode($request->getBody()->getContents(), true);
        //        dd(implode(',',$productTest['data']['ids']) );

        //        $request = $client->get('https://api31-core-useast1a.tiktokv.com/aweme/v1/feed/');
        //        $response= json_decode($request->getBody()->getContents(),true);
        //        dd($response);
    }
}
