<?php
class DocGenerator
{
    private $pdo;

    private $tableFormat =  "| Field | Type | Default | Comment  | Null | Key | \r\n".
                            "|---|---|---|---|---|---|\r\n";

    private $tables;

    public function __construct($dsn,$username,$password)
    {
        $this->pdo = new PDO($dsn,$username,$password);
    }

    protected function getTables()
    {
        $sql = "show tables";

        $statement = $this->pdo->query($sql,PDO::FETCH_ASSOC);

        $items = $statement->fetchAll();

        $tables = array_map(function($item){
            return current($item);
        },$items);

        return $tables;
    }

    protected function getTableInfo($table)
    {
        return '';
    }

    protected function getTableFields($table)
    {
        $sql = "show full columns from {$table}";

        echo $sql."\r\n";

        $statement = $this->pdo->query($sql,PDO::FETCH_ASSOC);

        if(!$statement){
            throw new Exception('Exception G');
        }

        return $statement->fetchAll();
    }

    /**
     * 输出文档
     */
    public function output($filename = '',$importTables = "*")
    {
        $tables = $this->getTables();
        $preStr = "";
        foreach ($tables as $table){
            if($importTables != "*" && !in_array($table,explode(",",$importTables))){
                continue;
            }

            try{
                $tableStr = "### {$table} \r\n\r\n";
                //$info = $this->getTableInfo('');
                //
                $fields = $this->getTableFields($table);

                $tableStr .= $this->tableFormat;
                foreach ($fields as $field){
                    $format = "|%s|%s|%s|%s|%s|%s|\r\n";
                    $row = sprintf($format,$field['Field'],$field['Type'],$field['Default']?$field['Default']:'-',$field['Comment']?$field['Comment']:'-',$field['Null'],$field['Key']?$field['Key']:'-');
                    $tableStr .= $row;
                }
                $preStr .= $tableStr;
            }catch (Exception $e){
                echo $e->getMessage();
            }
        }

        file_put_contents("./doc.md",$preStr);
    }
}