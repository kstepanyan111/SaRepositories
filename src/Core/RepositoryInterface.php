<?php

namespace Sa\Repositories\Core;

/**
 * Description of AbstractRepository
 *
 * @author maciek
 */
interface RepositoryInterface
{
    public function all(array $with = [], $columns = array('*'));

    public function paginate($perPage = 15, $columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id, $attribute = "id", $condition = '=');
    
    public function delete($ids);

    public function find($id, $columns = array('*'), $with = []);

    public function findBy($attribute, $value, $columns = array('*'));

    public function findByOrCreate($attribute, $value, $columns = array('*'));

    public function findByOrNew($attribute, $value, $columns = array('*'));

    public function makeQuery();

    public function makeModel();

    public function saveSearchData($searchData, $dataType, $entity_type, $entity_id);

    public function getTablePrefix();

    public function filterPaginate(array $input = [], array $with = [], $paginationSize = 15);

    public function filterGet(array $input = [], array $with = []);
}
