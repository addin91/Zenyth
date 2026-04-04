<?php

class JsonDB{
    private $file;

    public function __construct($file){
        $this->file = __DIR__ . "/json/" . $file . ".json";
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
    } 

    public function selectAll(){
        $json = file_get_contents($this->file);
        return json_decode($json, true) ?: [];
    }

    public function find($id){
        $data = $this->selectAll();
        return $data[$id] ?? null;
    }

    public function where($key, $value){
        $data = $this->selectAll();
        return array_filter($data, fn($item) => isset($item[$key]) && $item[$key] == $value);
    }

    public function whereData($data ,$key, $value){
        return array_filter($data, fn($item) => isset($item[$key]) && $item[$key] == $value);
    }

    public function update($id, $newData){
        $data = $this->selectAll();
        if(!isset($data[$id])) return false;
        $data[$id] = array_merge($data[$id] ?? [], $newData);
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }

    public function add($newData){
        $data = $this->selectAll();
        $ids = array_map('intval', array_keys($data));
        $newId = $ids ? max($ids) + 1 : 1;
        $newData["id"] = $newId;
        $data[$newId] = $newData;
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        return $newId;
    }

    public function delete($id){
        $data = $this->selectAll();
        if(isset($data[$id])){
            unset($data[$id]);
            file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
            return true;
        }
        return false;
    }
}

?>