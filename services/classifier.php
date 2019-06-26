<?php

require "../vendor/autoload.php";
require "util.php";

$classifier = new System\Classifier();

$mongo = new System\Mongo();

$tickets = json_decode(file_get_contents('../files/tickets.json'), true);

$classifier->classify($tickets);

$mongo->dropCollection('tickets');

$mongo->insert('tickets', $tickets, true);

file_put_contents("../files/classified_tickets.json", json_encode($tickets));

$total = count($tickets);
$correct = array();
$wrong = array();

foreach ($tickets as $index => $ticket) {
    if ($ticket['priority'] == $ticket['suggested_priority']) {
        $correct[] = $ticket;
    } else {
        $wrong[] = $ticket;
    }
}

$accuracy = (count($correct) / $total * 100) . "%";

$response = array(
    "accuracy" => $accuracy,
    "summary" => ["total" => $total, "correct" => count($correct), "wrong" => count($wrong)],
    "corret_tickets" => $correct,
    "wrong_tickets" => $wrong
);

response($response);