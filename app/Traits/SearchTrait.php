<?php
namespace  App\Traits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use  Arr;

trait  SearchTrait
{
	/**
     * Get list tables with join conditions
     *
     * @return Array
     */
    public function getJoins()
    {
      return  Arr::get($this->searchable, 'joins', []);
	}
	
	/**
     * Scope to join table with corresponding conditions from getJoins()
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMakeJoins(Builder $query)
    {
      foreach ($this->getJoins() as $table => $keys) {
        $query->leftJoin($table, function ($join) use ($keys) {
          $join->on($keys[0], '=', $keys[1]);
        });
      }
    }
	
	/**
     * Get all fields, which can be search in this tables and relation table
     * @return Array
     */
	public function getSearchFields()
	{
		$model = $this;
		$fields = Arr::get($model->searchable, 'columns', []);
		if (empty($fields)) {
			$fields = Schema::getColumnListing($model->getTable());
			$others[] = $model->primaryKey;
			$others[] = $model->getUpdatedAtColumn() ?: 'created_at';
			$others[] = $model->getCreatedAtColumn() ?: 'updated_at';
			$others[] = method_exists($model, 'getDeletedAtColumn')
			? $model->getDeletedAtColumn()
			: 'deleted_at';
			$fields = array_diff($fields, $model->getHidden(), $others);
		}
		return $fields;
	}

	/**
	* Scope models are used to search by $keyword for fields on the current table and relational tables
	* @param \Illuminate\Database\Eloquent\Builder|static $query
	* @param string $keyword
	* @param boolean $matchAllFields
	* @return \Illuminate\Database\Eloquent\Builder
	*/
	public function scopeSearch($query, $keyword, $matchAllFields = false)
	{
		if (empty($keyword)) {
			return $query;
		}
		$query = $query->makeJoins();
		$query = $query->select($this->getTable() .  '.*')->distinct();
		$query = $query->where(function ($query) use ($keyword, $matchAllFields) {
			$keyword = preg_replace('/\s+/', '%', $keyword);
			foreach ($this->getSearchFields() as $field) {
				if ($matchAllFields) {
					$query->where($field, 'LIKE', "%$keyword%");
				} else 
					$query->orWhere($field, 'LIKE', "%$keyword%");
				}
			}
		);
		return $query;
	}

	/**
	* Scope models are used to search by $keyword for fields on the current table
	* @param \Illuminate\Database\Eloquent\Builder|static $query
	* @param string $keyword
	* @param boolean $matchAllFields
	* @return \Illuminate\Database\Eloquent\Builder
	*/
	public function scopeSearchNoRelation($query, $keyword, $matchAllFields = false)
	{
		if (empty($keyword)) {
			return  $query;
		}
		$query = $query->where(function ($query) use ($keyword, $matchAllFields) {
			$keyword = preg_replace('/\s+/', '%', $keyword);
			foreach ($this->getSearchFields() as $field) {
				if ($matchAllFields) {
					$query->where($field, 'LIKE', "%$keyword%");
				} else 
					$query->orWhere($field, 'LIKE', "%$keyword%");
				}
			}
		);
		return $query;
	}
}