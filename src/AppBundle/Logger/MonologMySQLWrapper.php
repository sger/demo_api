<?php

namespace AppBundle\Logger;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use PDO;
use PDOStatement;

class MonologMySQLWrapper extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var bool check if we are connected to the database.
     */
    private $isConnected = false;

    /**
     * @var PDO pdo object.
     */
    protected $pdo;

    /**
     * @var PDOStatement.
     */
    private $statement;

    /**
     * @var string table name.
     */
    private $table = 'logs';

    /**
     * @var array default fields for the database.
     */
    private $defaultfields = ['channel', 'level', 'message', 'time'];

    /**
     * @var string[] additional fields for the database.
     *
     */
    private $additionalFields = [];


    /**
     * Constructor of this class, sets the PDO and calls parent constructor
     *
     * @param PDO $pdo                  PDO Connector for the database
     * @param bool $table               Table in the database to store the logs in
     * @param array $additionalFields   Additional Context Parameters to store in database
     * @param bool|int $level           Debug level which this handler should store
     * @param bool $bubble
     */
    public function __construct(PDO $pdo = null, $table, $additionalFields = [], $level = Logger::DEBUG, $bubble = true) {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        }
        $this->table = $table;
        $this->additionalFields = $additionalFields;
        parent::__construct($level, $bubble);
    }

    /**
     * If the table not exists set up a new connection.
     *
     * @return void
     */
    private function createMySQLConnection()
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `'.$this->table.'` '
            .'(id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, channel VARCHAR(255), level INTEGER, message LONGTEXT, time INTEGER UNSIGNED, INDEX(channel) USING HASH, INDEX(level) USING HASH, INDEX(time) USING BTREE)'
        );

        $actualFields = [];

        $rs = $this->pdo->query('SELECT * FROM `'.$this->table.'` LIMIT 0');

        for ($i = 0; $i < $rs->columnCount(); $i++) {
            $col = $rs->getColumnMeta($i);
            $actualFields[] = $col['name'];
        }

        $removedColumns = array_diff(
            $actualFields,
            $this->additionalFields,
            $this->defaultfields
        );
        $addedColumns = array_diff($this->additionalFields, $actualFields);

        if (!empty($removedColumns)) {
            foreach ($removedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `'.$this->table.'` DROP `'.$c.'`;');
            }
        }

        if (!empty($addedColumns)) {
            foreach ($addedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `'.$this->table.'` add `'.$c.'` TEXT NULL DEFAULT NULL;');
            }
        }

        $this->defaultfields = array_merge($this->defaultfields, $this->additionalFields);

        $this->isConnected = true;
    }

    /**
     * Prepare the sql statements.
     *
     * @return void
     */
    private function prepareStatement()
    {
        $columns = "";
        $fields  = "";

        foreach ($this->fields as $key => $f) {
            if ($key == 0) {
                $columns .= "$f";
                $fields .= ":$f";
                continue;
            }

            $columns .= ", $f";
            $fields .= ", :$f";
        }

        $this->statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->table . '` (' . $columns . ') VALUES (' . $fields . ')'
        );
    }


    /**
     * Writes the records to the database.
     *
     * @param  array
     * @return void
     */
    protected function write(array $record)
    {
        if (!$this->isConnected) {
            $this->createMySQLConnection();
        }

        $this->fields = $this->defaultfields;

        if (isset($record['extra'])) {
            $record['context'] = array_merge($record['context'], $record['extra']);
        }

        $contentArray = array_merge([
              'channel' => $record['channel'],
              'level'   => $record['level'],
              'message' => $record['message'],
              'time'    => $record['datetime']->format('U')
            ], $record['context']);

        foreach($contentArray as $key => $context) {
            if (! in_array($key, $this->fields)) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields)]);
                continue;
            }

            if ($context === null) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields)]);
            }
        }

        $this->prepareStatement();

        $contentArray = $contentArray + array_combine(
            $this->additionalFields,
            array_fill(0, count($this->additionalFields), null)
        );

        $this->statement->execute($contentArray);
    }
}
