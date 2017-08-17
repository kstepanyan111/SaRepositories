<?php

namespace Sa\Repositories\Core;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of AbstractRepository
 *
 * @author maciek
 */
abstract class RepositoryAbstract implements RepositoryInterface
{

    /**
     * Order directions
     */
    CONST ORDER_ASC = 'ASC';
    CONST ORDER_DESC = 'DESC';

    /**
     * @var App
     */
    private $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * RepositoryAbstract constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeQuery();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function getModelName();

    /**
     * Get order directions list
     *
     * @return array
     */
    public static function getOrderDirections()
    {
        return [
            static::ORDER_ASC,
            static::ORDER_DESC,
            strtolower(static::ORDER_ASC),
            strtolower(static::ORDER_DESC),
        ];
    }

    /**
     * @param array $columns
     * @param array $with
     * @return mixed
     */
    public function all(array $with = [], $columns = array('*'))
    {
        return $this->model->with($with)->get($columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = array('*'))
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @param string $condition
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id", $condition = '=')
    {
        $query = $this->makeQuery();

        $data = $this->makeModel()->fill($data)->toArray();

        return $query->where($attribute, $condition, $id)->update($data);
    }

    /**
     * Delete entities
     *
     * @param $ids
     * @return mixed
     */
    public function delete($ids)
    {

        $count = 0;

        $ids = is_array($ids) ? $ids : func_get_args();

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $key = with($instance = $this->makeModel())->getKeyName();

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $id
     * @param array $columns
     * @param array $with
     * @return mixed
     */
    public function find($id, $columns = array('*'), $with = [])
    {
        return $this->model->with($with)->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByOrCreate($attribute, $value, $columns = array('*'))
    {
        $data = $this->model->where($attribute, '=', $value)->first($columns);

        if (empty($data)) {
            return $data = $this->model->create([$attribute => $value])->first($columns);
        }

        return $data;
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByOrNew($attribute, $value, $columns = array('*'))
    {
        $data = $this->model->where($attribute, '=', $value)->first($columns);

        if (empty($data)) {
            $data = $this->model->fill([$attribute => $value])->first($columns);
        }

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeQuery()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model->newQuery();
    }

    /**
     * @return Model
     *
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * Get table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return strtok($this->makeModel()->getKeyName(), '_');
    }

    /**
     * Filter and paginate results
     *
     * @param array $input
     * @param array $with
     * @param int $paginationSize
     * @return mixed
     */
    public function filterPaginate(array $input = [], array $with = [], $paginationSize = 15)
    {
        return $this->filter($input, $with)->paginate(!empty($input['limit']) ? (int)$input['limit'] : $paginationSize);
    }

    /**
     * Filter and paginate results
     *
     * @param array $input
     * @param array $with
     * @return mixed
     */
    public function filterGet(array $input = [], array $with = [])
    {
        return $this->filter($input, $with)->get();
    }

    /**
     * Filter entities
     *
     * @param array $input
     * @param array $with
     * @return Model
     */
    public function filter(array $input = [], array $with = [])
    {
        $orderColumns = getValue($input, 'order.col', $this->getTablePrefix() . '_created_at');

        if (str_contains($orderColumns, ',')) {
            $orderColumns = explode(',', $orderColumns);
            $orderColumns = array_filter($orderColumns);
            $orderColumns = array_map('trim', $orderColumns);
        } else {
            $orderColumns = [$orderColumns];
        }

        foreach ($orderColumns as $index => $orderColumn) {
            if (!$this->makeModel()->isFillable($orderColumn)) {
                unset($orderColumns[$index]);
            }
        }


        if (empty($orderColumns)) {
            $orderColumns = [$this->getTablePrefix() . '_created_at'];
        }


        $orderDirection = getValue($input, 'order.dir', static::ORDER_DESC);

        if (!in_array(strtoupper($orderDirection), static::getOrderDirections())) {
            $orderDirection = static::ORDER_DESC;
        }

        $filters = (array)getValue($input, 'filters', []);

        $query = $this->makeQuery()
            ->filter($filters)
            ->with($with);

        foreach ($orderColumns as $orderColumn) {
            $query->orderBy($orderColumn, $orderDirection);
        }

        return $query;
    }

}
