<?php

function createSql($tableName, $api, $create = 1)
{
    $url = "https://open-api.pinduoduo.com/pop/doc/info/get";
    $data = [
        'id' => $api
    ];
    $str = postUrl($url, $data);
    $res = json_decode($str, true);
    if ($create) {
        $strLong = "CREATE TABLE " . $tableName . " (id int PRIMARY KEY AUTO_INCREMENT); " . PHP_EOL;
    } else {
        $strLong = "";
    }

    foreach ($res['result']['requestParamList'] as $item) {
        $sql = "ALTER TABLE " . $tableName . " ADD COLUMN `" . $item['paramName'] . "` ";
        switch ($item['paramType']) {
            case 'LONG':
                if (strpos($item['paramName'], 'id') !== false) {
                    $sql .= " int(11) NULL DEFAULT 0 ";
                } elseif (strpos($item['paramName'], 'price') !== false) {
                    $sql .= " numeric(10, 2) NULL DEFAULT 0 ";
                } else {
                    $sql .= " varchar(100) NULL DEFAULT '' ";
                }
                break;
            case 'INTEGER':
                $sql .= " int(11) NULL DEFAULT 0 ";
                break;
            case 'OBJECT[]':
                $sql .= " text NULL ";
                break;
            case 'STRING':
                $sql .= " varchar(1000) NULL DEFAULT '' ";
                break;
            case 'STRING[]':
                $sql .= " text NULL ";
                break;
            case 'OBJECT':
                $sql .= " text NULL ";
                break;
            case 'BOOLEAN':
                $sql .= " enum('true','false') NOT NULL ";
                break;
        }
        $sql .= " COMMENT '" . $item['paramDesc'] . "' ;" . PHP_EOL;
        $strLong .= $sql;

    }
    echo '<pre>';
    print_r($strLong);
    exit;
}


function createTable()
{
    //表名 => api
    $tableArr = [
        'goods_test' => 'pdd.goods.information.update',
    ];
    foreach ($tableArr as $key => $item) {
        createSql($key, $item, 0);
    }
}

createTable();

function postUrl($url, $data)
{
    $data = json_encode($data);
    $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
