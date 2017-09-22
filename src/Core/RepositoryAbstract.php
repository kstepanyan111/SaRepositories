<?php

namespace Sa\Repositories\Core;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of AbstractRepository
 *
 * @author Sergii Akimov
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
     * @throws RepositoryException
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->makeModel();
    }

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
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @return Model
     *
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $this->model = $this->app->make($this->model());

        if (!$this->model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model;
    }

    /**
     * Reset model
     *
     * @return Model
     * @throws RepositoryException
     */
    public function resetModel()
    {
        return $this->makeModel();
    }

    /**
     * Get model
     *
     * @return Model
     */
    public function getModel()
    {
        if ($this->model instanceof Model) {
            return $this->model;
        } elseif ($this->model instanceof Builder) {
            return $this->model->getModel();
        }

        return $this->model;
    }

    /**
     * Key table primary key name
     *
     * @return mixed
     */
    public function getKeyName()
    {
        if ($this->model instanceof Model) {
            return $this->model->getKeyName();
        } elseif ($this->model instanceof Builder) {
            return $this->model->getModel()->getKeyName();
        }

        return 'id';
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Where has
     *
     * @param $relation
     * @param \Closure|null $callback
     * @param string $operator
     * @param int $count
     * @return $this
     */
    public function has($relation, \Closure $callback = null, $operator = '>=', $count = 1)
    {
        $this->model = $this->model->whereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Order by
     *
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Set hidden fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model = $this->model->setHidden($fields);

        return $this;
    }

    /**
     * Set visible fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model = $this->model->setVisible($fields);

        return $this;
    }

    /**
     * Remove global scopes
     *
     * @param array|null $scopes
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null)
    {
        $this->model = $this->model->withoutGlobalScopes($scopes);

        return $this;
    }

    /**
     * Filter entities
     *
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters = [])
    {
        $this->model = $this->model->filter($filters);

        return $this;
    }

    /**
     * Get all records
     *
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    public function all($columns = array('*'))
    {
        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();

        return $results;
    }

    /**
     * Paginate results
     *
     * @param int $perPage
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    public function paginate($perPage = 15, $columns = array('*'))
    {
        $results = $this->model->paginate($perPage, $columns);

        $this->resetModel();

        return $results;
    }


    /**
     * Pluck columns
     *
     * @param $value
     * @param null $key
     * @return mixed
     * @throws RepositoryException
     */
    public function pluck($value, $key = null)
    {
        $results = $this->model->pluck($value, $key);

        $this->resetModel();

        return $results;
    }

    /**
     * Create new entity
     *
     * @param array $data
     * @return mixed
     * @throws RepositoryException
     */
    public function create(array $data)
    {
        $entity = $this->model->create($data);

        $this->resetModel();

        return $entity;
    }

    /**
     * Update entity
     *
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     * @throws RepositoryException
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $updated = 0;

        foreach ($this->model->where($attribute, '=', $id)->get() as $entity) {
            $entity->fill($data);
            $entity->save();

            $updated++;
        }

        $this->resetModel();

        return $updated;
    }

    /**
     * Update or create new entity
     *
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     * @throws RepositoryException
     */
    public function createOrUpdate(array $data, $id, $attribute = 'id')
    {
        if ((int)$id > 0) {
            $this->update($data, $id, $attribute);

            return $this->findBy($attribute, $id);
        } else {
            return $this->create($data);
        }
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
        $instance = $this->model;
        $key = $this->getKeyName();

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Find record by id
     *
     * @param $id
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    public function find($id, $columns = ['*'])
    {
        $entity = $this->model->find($id, $columns);

        $this->resetModel();

        return $entity;
    }

    /**
     * Find record by criteria
     *
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        $entity = $this->model->where($attribute, '=', $value)->first($columns);

        $this->resetModel();

        return $entity;
    }

    /**
     * Find record by criteria Or create new
     *
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    public function findByOrCreate($attribute, $value, $columns = ['*'])
    {
        if (!$entity = $this->findBy($attribute, $value, $columns)) {
            return $this->create([$attribute => $value]);
        }

        return $entity;
    }

}