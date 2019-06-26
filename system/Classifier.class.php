<?php

namespace System;

use DateTime;

class Classifier
{

    private $words;
    private $avgDays;

    public function __construct()
    {
        $this->words = [
            "reclamacao",
            "estragar",
            "defeitos",
            "garantia",
            "troca",
            "solucao",
            "debitada",
            "fatura",
            "cartao",
            "providencias",
            "pagamento",
            "parcela",
            "resolver",
            "tentativa",
            "nao",
            "troco",
            "cancelamento",
            "problema",
            "fuciona",
            "porem",
            "entretanto",
            "diferenca",
            "mas",
            "corrigir",
            "dia",
            "reenviado",
            "trocamos",
            "defeito",
            "diferente",
            "prazo",
            "verificar",
            "ainda",
            "pois",
            "endereco",
            "aconteceu"
        ];
    }

    private function calcAvgDays($tickets)
    {
        $time = 0;
        foreach ($tickets as $index => $ticket) {
            $dateStart = new DateTime($ticket['DateCreate']);
            $dateEnd = new DateTime($ticket['DateUpdate']);

            $dateInterval = $dateStart->diff($dateEnd);
            $days = $dateInterval->days;
            $time += $days;
        }

        $this->avgDays = round($time / count($tickets), 2);
    }

    private function normalizeString($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(é|è|ê|ë)/", "/(í|ì|î|ï)/", "/(ó|ò|õ|ô|ö)/", "/(ú|ù|û|ü)/", "/(ç)/"), explode(" ", "a e i o u c"), strtolower($string));
    }

    private function dateCheck(&$ticket)
    {
        $dateStart = new DateTime($ticket['DateCreate']);
        $dateEnd = new DateTime($ticket['DateUpdate']);
        $dateInterval = $dateStart->diff($dateEnd);
        $days = $dateInterval->days;

        $ticket['classification']['days'] = $days;
        $ticket['classification']['avgDays'] = $this->avgDays;

        return ($days > $this->avgDays) ? round(($days / $this->avgDays) - 1, 2) : 0;
    }

    private function textCheck(&$ticket)
    {
        $score = 0;
        foreach ($ticket['Interactions'] as $index => $interaction) {
            foreach ($this->words as $index2 => $word) {
                $pattern = "/{$word}/";
                $subtitle = $this->normalizeString($interaction['Subject']);
                $message = $this->normalizeString($interaction['Message']);

                preg_match($pattern, $subtitle, $subtitleMatches);

                preg_match($pattern, $message, $messageMatches);

                if (count($subtitleMatches) != 0) {
                    if (!in_array($word, $ticket['classification']['words'])) {
                        $ticket['classification']['words'][] = $word;
                        $score++;
                    }
                }

                if (count($messageMatches) != 0) {
                    if (!in_array($word, $ticket['classification']['words'])) {
                        $ticket['classification']['words'][] = $word;
                        $score++;
                    }
                }
            }
        }
        return $score;
    }

    public function classify(&$tickets)
    {
        $this->calcavgDays($tickets);

        foreach ($tickets as $index => $ticket) {
            $tickets[$index]['classification'] = array();
            $tickets[$index]['classification']['words'] = array();

            $tickets[$index]['classification']['scoreDays'] = $this->dateCheck($tickets[$index]);
            $tickets[$index]['classification']['scoreText'] = $this->textCheck($tickets[$index]);

            $tickets[$index]['score'] = $tickets[$index]['classification']['scoreDays'] + $tickets[$index]['classification']['scoreText'];

            if ($tickets[$index]['classification']['scoreText'] > 3) {
                $tickets[$index]['suggested_priority'] = 'Alta';
            } else {
                if ($tickets[$index]['classification']['scoreDays'] >= 0.11) {
                    $tickets[$index]['suggested_priority'] = 'Alta';
                } else {
                    $tickets[$index]['suggested_priority'] = 'Normal';
                }
            }
        }
    }
}