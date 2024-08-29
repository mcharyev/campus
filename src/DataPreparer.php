<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

use App\Entity\Group;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

/**
 * Description of DataPreparer
 *
 * @author nazar
 */
class DataPreparer {

    public function __construct() {
        
    }

//put your code here
    public function prepareQuery(string $tableName, Request $request, Connection $connection): Response {
        try {
            $query_params = [
                'table' => $tableName,
                'startIndex' => $request->attributes->get('startIndex'),
                'pageSize' => $request->attributes->get('pageSize'),
                'sorting' => $request->attributes->get('sorting'),
                'searchField' => $request->attributes->get('searchField'),
                'searchValue' => $request->attributes->get('searchValue')
            ];
            //throw new \Exception('Error: '.json_encode($repository->getRecordCount($query_fields)));
            $stmt = $connection->prepare($this->getCountSqlString($query_params));
            $stmt->execute();
            $result = $stmt->fetch();
            $recordCount = intval($result['RecordCount']);
            //Get records from database
            $stmt = $connection->prepare($sql = "SET @row_number := 0;");
            $stmt->execute();

            $stmt = $connection->prepare($this->getRecordsSqlString($query_params));
            $stmt->execute();
            $results = $stmt->fetchAll();
            //throw new \Exception('Value of id is: '.json_encode($results));
            //Return result
            $result_array = [
                'Result' => "OK",
                'TotalRecordCount' => $recordCount,
                'Records' => $results
            ];
        } catch (\Exception $e) {
            //Return error message
            $result_array = [
                'Result' => "ERROR",
                'Message' => $e->getMessage()
            ];
        }

        $response = new Response(json_encode($result_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getCountSqlString(array $params = null): string {
        $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" . $params['table'] . "`";
        if (!empty($params['searchField']) and!empty($params['searchValue'])) {
            $sql = "SELECT COUNT(" . $params['table'] . ".id) AS RecordCount FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%'";
        }
        return $sql;
    }

    public function getRecordsSqlString(array $params = null): string {
        if (!empty($params['searchField']) and!empty($params['searchValue'])) {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` WHERE " . $params['searchField'] . " LIKE '%" . $params['searchValue'] . "%' ORDER BY " . $params['sorting'] . " LIMIT " . $params['startIndex'] . ", " . $params['pageSize'];
        } else {
            $sql = "SELECT *, @row_number:=@row_number+1 AS row_number FROM `" . $params['table'] . "` ORDER BY " . $params['sorting'] . " LIMIT " . $params['startIndex'] . ", " . $params['pageSize'];
        }
        return $sql;
    }

    public function getLastIdString(string $tableName): string {
        return "SELECT * FROM `" . $tableName . "` WHERE " . $tableName . ".id = LAST_INSERT_ID();";
    }

    public function deleteEntity(string $tableName, string $id, Connection $connection): int {
        $stmt = $connection->prepare("DELETE FROM `" . $tableName . "` WHERE " . $tableName . ".id = '" . $id . "'");
        $stmt->execute();
        return $stmt->rowCount();
    }

}
