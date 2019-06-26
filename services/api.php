<?php

require "../vendor/autoload.php";
require "util.php";

$options = array();
$filter = array();

$options['projection'] = ['_id' => false];

if (isset($_REQUEST["orderBy"])) {
    $fields = array('DateCreate', 'DateUpdate', 'priority');
    $modes = array('ASC', 'DESC');

    $orderBy = json_decode($_REQUEST["orderBy"], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        response(["message" => "The orderBy parameter must be a JSON"], 400);
    }

    if (!in_array($orderBy['field'], $fields) || !in_array($orderBy['mode'], $modes)) {
        response(["message" => "Invalid values in orderBy parameter", "valid_fields" => $fields, "valid_modes" => $modes], 400);
    }

    $attr = ($orderBy['field'] == "priority") ? "score" : $orderBy['field'];
    $mode = ($orderBy['mode'] == "ASC") ? 1 : -1;

    $sort = array();
    $sort[$attr] = $mode;
    $options["sort"] = $sort;
}

if (isset($_REQUEST["filter"])) {

    $filterArr = json_decode($_REQUEST["filter"], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        response(["message" => "The filter parameter must be a JSON"], 400);
    }

    if (isset($filterArr['start']) && $filterArr['start'] != "" && validateDate($filterArr['start'])) {
        $filter['DateCreate'] = array();
        $filter['DateCreate']['$gt'] = $filterArr['start'] . " 00:00:00";
    }

    if (isset($filterArr['end']) && $filterArr['end'] != "" && validateDate($filterArr['end'])) {
        if (!isset($filter['DateCreate'])) {
            $filter['DateCreate'] = array();
        }
        $filter['DateCreate']['$lt'] = $filterArr['end'] . " 23:59:59";
    }

    if (isset($filterArr['priority']) && $filterArr['priority'] != "") {
        $filter['priority'] = $filterArr['priority'];
    }
}


if (isset($_REQUEST["pagination"])) {
    $paginationArr = json_decode($_REQUEST["pagination"], true);
    $fields = array('page', 'qtd');

    if (json_last_error() !== JSON_ERROR_NONE) {
        response(["message" => "The pagination parameter must be a JSON"], 400);
    }

    if (count(array_diff(array_keys($paginationArr), $fields)) != 0) {
        response(["message" => "Invalid fields in pagination parameter.", "valid_fields" => $fields], 400);
    } else if (!is_numeric($paginationArr['page']) || !is_numeric($paginationArr['qtd'])) {
        response(["message" => "Invalid values in pagination parameter. The fields values must be a numbers"], 400);
    }

    $currentPage = intval($paginationArr['page']);
    $limit = intval($paginationArr['qtd']);

} else {
    //default values
    $currentPage = 1;
    $limit = 10;
}

$options['skip'] = ($currentPage - 1) * $limit;
$options['limit'] = $limit;

$mongo = new System\Mongo();

$tickets = $mongo->findAll("tickets", $filter, $options);

$pagination = array();
$pagination['total_of_tickets'] = $mongo->count("tickets", $filter);
$pagination['number_of_pages'] = ceil($mongo->count("tickets", $filter) / $limit);
$pagination['current_page'] = $currentPage;
$pagination['tickets_per_page'] = $limit;

$response = array();
$response['pagination'] = $pagination;
$response['data'] = $tickets->toArray();

response($response);