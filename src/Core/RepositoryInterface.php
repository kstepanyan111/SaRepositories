<?php

namespace Sa\Repositories\Core;

/**
 * Description of AbstractRepository
 *
 * @author maciek
 */
interface RepositoryInterface
{
    public function makeModel();

    public function getModel();

    public function resetModel();

    public function getKeyName();

    public function with($relations);

    public function has($relation, \Closure $callback = null, $operator = '>=', $count = 1);

    public function orderBy($column, $direction = 'asc');

    public function hidden(array $fields);

    public function visible(array $fields);

    public function withoutGlobalScopes(array $scopes = null);

    public function all($columns = ['*']);

    public function paginate($perPage = 15, $columns = ['*']);

    public function pluck($value, $key = null);

    public function create(array $data);

    public function update(array $data, $id, $attribute = "id");

    public function delete($ids);

    public function find($id, $columns = ['*']);

    public function findBy($attribute, $value, $columns = ['*']);

    public function findByOrCreate($attribute, $value, $columns = ['*']);

    public function filter(array $filters = []);
}
