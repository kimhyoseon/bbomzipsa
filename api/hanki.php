<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/hanki.php');
    $hanki = new Hanki();

    // $_POST['year'] = '2020';
    // $_POST['month'] = '5';
    // echo '<pre>';
    // print_r($hanki->getMenu($_POST));
    // echo '</pre>';
    // exit();

    if (empty($_POST)) {
        throw new Exception(null, 400);
    }

    if ($_POST['menu'] == 'view') {
        $data = json_encode($hanki->getMenuExist($_POST));
    } else if ($_POST['menu'] == 'new') {
        $data = json_encode($hanki->getMenu($_POST));
    } else if ($_POST['menu'] == 'order') {
        $data = json_encode($hanki->getOrder($_POST));
    } else if ($_POST['menu'] == 'editorder') {
        if (empty($_POST['data'])) {
            throw new Exception(null, 400);
        }

        $data['data'] = $_POST['data'];
        $data['result'] = $hanki->editOrder($data['data']);
        $data = json_encode($data);
    } else if ($_POST['menu'] == 'saveNew') {
        if (empty($_POST['data'])) {
            throw new Exception(null, 400);
        }

        $data['data'] = $_POST['data'];
        $data['result'] = $hanki->saveJson($data['data']);
        $data = json_encode($data);
    } else if ($_POST['menu'] == 'saveNewOver') {
        if (empty($_POST['data'])) {
            throw new Exception(null, 400);
        }

        $data['data'] = $_POST['data'];
        $data['result'] = $hanki->saveJson($data['data'], true);
        $data = json_encode($data);
    }

    if (empty($data)) {
        throw new Exception(null, 400);
    }

    echo $data;
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>
