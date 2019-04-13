<?php

namespace Core\Table;

use Core\Database\Database;

class Table
{
    protected $table;
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
        if (is_null($this->table)) {
            $parts = explode('\\', get_class($this));
            $class_name = end($parts);
            $this->table = strtolower(str_replace('Model', '', $class_name)) . 's';
        }
    }

    public function count($id){
        return $this->query('SELECT COUNT (*) FROM ' . $this->table . ' WHERE id = ?',[$id]);
    }

    public function all()
    {
        return $this->query('SELECT * FROM ' . $this->table);
    }

    public function find($id)
    {
        return $this->query("
            SELECT * 
            FROM {$this->table} 
            WHERE id = ?", [$id], true);
    }

    public function delete($id)
    {

        return $this->query("DELETE FROM {$this->table} WHERE id = ? ",[$id] , true);
    }

    public function create($fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_parts = implode(', ', $sql_parts);
        return $this->query("INSERT INTO {$this->table} SET $sql_parts", $attributes, true);
    }

    public function update($id, $fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $attributes[] = $id;
        $sql_parts = implode(', ', $sql_parts);
        return $this->query("UPDATE {$this->table} SET $sql_parts WHERE id = ? ", $attributes, true);
    }

    public function extract($key, $value)
    {
        $records = $this->all();
        foreach ($records as $v) {
            $return[$v->$key] = $v->$value;
        }
        return $return;
    }

    public function query($statement, $attributes = null, $one = false)
    {
        if ($attributes) {
            return $this->db->prepare(
                $statement,
                $attributes,
                str_replace('Model', 'Entity', get_class($this)),
                $one
            );
        } else {
            return $this->db->query(
                $statement,
                str_replace('Model',
                    'Entity', get_class($this)),
                $one
            );
        }
    }

}