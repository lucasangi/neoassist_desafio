<?php

namespace System;

class Mongo
{

    private $host, $port, $user, $password, $dataBase, $conn, $db;

    public function __construct()
    {
        $this->host = "mongo";
        $this->port = "27017";
        $this->user = "neoassist";
        $this->password = "tickets";
        $this->dataBase = "neoassist";

        try {
            $this->conn = (new \MongoDB\Client("mongodb://{$this->user}:{$this->password}@{$this->host}:{$this->port}"));
            $this->db = $this->conn->{$this->dataBase};
        } catch (\MongoDB\Exception\InvalidArgumentException $e) {
            http_response_code(500);
            echo json_encode(["Error" => $e->getMessage()]);
            exit;
        }
    }

    public function insert($collectionName, $docs, $isBatch = false)
    {

        $collection = $this->db->{$collectionName};

        try {
            if ($isBatch) {
                $rsInsert = $collection->insertMany($docs);
            } else {
                $rsInsert = $collection->insertOne($docs);
            }

            return $rsInsert->getInsertedCount();

        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {

            $textError = "";
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {

                $textError = sprintf("%s (%d)\n",
                    $writeConcernError->getMessage(),
                    $writeConcernError->getCode()
                );
            }

            foreach ($result->getWriteErrors() as $writeError) {
                $textError = sprintf("Operation#%d: %s (%d)\n",
                    $writeError->getIndex(),
                    $writeError->getMessage(),
                    $writeError->getCode()
                );
            }

            http_response_code(500);
            echo json_encode(["Error" => $textError]);
            exit;

        } catch (\MongoDB\Driver\Exception\Exception $e) {
            http_response_code(500);
            echo json_encode(["Error" => sprintf("Other error: %s\n", $e->getMessage())]);
            exit;
        }
    }

    public function findAll($collectionName, $filter = [], $options = [])
    {
        $collection = $this->db->{$collectionName};
        return $collection->find($filter, $options);
    }

    public function count($collectionName, $filter)
    {
        $collection = $this->db->{$collectionName};
        return $collection->count($filter);
    }

    public function dropCollection($collectionName)
    {
        $this->db->{$collectionName}->drop();
    }
}