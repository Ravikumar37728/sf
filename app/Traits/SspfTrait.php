<?php

namespace App\Traits;

use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

trait SspfTrait
{
    use MessagesTrait,
        CodeGeneraterTrait;

    public function scopeApplySspf($query, $model, $request, $excel)
    {
        $model = new $model();
        $sortable = $searchable = $model->sortable;
        $filterable = $model->dates;
        $orderable = ["ASC", "DESC", "asc", "desc"];
        // Searching
        if ($request->has('search') && !is_null(trim($request['search']))) {
            $baseClass = class_basename($model);
            $query = $model::whereLike($searchable, $request['search'], $baseClass);
        }
        // Sorting
        if ($request->has('sort') && !is_null(trim($request['sort'])) && $request->has('order_by') && !is_null(trim($request['order_by']))) {
            $order_by = in_array($request['order_by'], $orderable) ? $request['order_by'] : "DESC";
            $sort_column = in_array($request['sort'], $sortable) ? $request['sort'] : "id";
            $query->orderBy($sort_column, $order_by);
        }
        // Filter
        if ($request->has('filter') && !is_null($request['filter'])) {
            $filters = json_decode($request['filter'], true);
            $query->where(function ($query) use ($filters, $sortable, $filterable) {
                // Apply filter for each values
                foreach ($filters as $column => $value) {
                    if (!is_array($value)) {
                        if (in_array($column, $filterable)) {
                            $new_date = [];
                            $dates = explode("to", $value);
                            $new_date[0] = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay()->toDateTimeString();
                            $new_date[1] = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay()->toDateTimeString();
                            $query->whereBetween($column, $new_date);
                        } else {
                            $query->where($column, $value);
                        }
                    } else if ($value !== "") {
                        if (in_array($column, $sortable)) {
                            $query->whereIn($column, $value);
                        }
                    }
                }
            });
        }
        // Tag
        $tags = json_decode($request->tags, true);
        if (!is_null($tags) && !empty($tags['tags'])) {
            $ids = Tag::whereIn("name", $tags['tags'])->join('taggables', 'tags.id', '=', 'taggables.tag_id')->get()->pluck('taggable_id')
                ->unique()->toArray();
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }
        // Pagination
        return ($request->has('page') && !is_null(trim($request['page'])) && $request->has('per_page') && !is_null(trim($request['per_page'])))
            ? $query->paginate($request['per_page'])
            : $query->paginate();
    }

    public function customSSPF($model, $collection, $request)
    {
        $model = new $model();
        $searchable = $model->sortable;
        $table = $model->getTable();
        $filterable = $model->dates;

        if ($request->get('is_light', false)) {
            return $model->select($model->light)->get();
        }

        if (!is_null($request['search'])) {
            $baseClass = class_basename($model);
            $collection = $model::whereLike($searchable, $request['search'], $baseClass);
        }

        if ($request->has('sort') && !is_null($request['sort']) && $request->has('order_by') && !is_null($request['order_by'])) {
            $order_by = in_array($request['order_by'], ["ASC", "DESC", "asc", "desc"]) ? $request['order_by'] : "DESC";
            $collection = $collection->orderBy($request['sort'], $order_by);
        }
        if ($request->has('filter') && !is_null($request['filter'])) {
            $filters = json_decode($request['filter'], true);
            $collection = $collection->where(function ($collection) use ($filters, $searchable, $filterable, $table) {
                // Apply filter for each values
                foreach ($filters as $column => $value) {
                    if (!is_array($value)) {
                        if (in_array($column, $filterable)) {
                            $new_date = [];
                            $dates = explode("to", $value);
                            $new_date[0] = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay()->toDateTimeString();
                            $new_date[1] = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay()->toDateTimeString();
                            $collection = $collection->whereBetween($table . '.' . $column, $new_date);
                        } else {
                            $collection = $collection->where($column, $value);
                        }
                    } else if ($value !== "") {
                        if (in_array($column, $searchable)) {
                            $collection = $collection->whereIn($column, $value);
                        }
                    }
                }
            });
        }
        // Tags
        $tags = json_decode($request->tags, true);
        if (!is_null($tags) && !empty($tags['tags'])) {
            $ids = Tag::whereIn("name", $tags['tags'])->join('taggables', 'tags.id', '=', 'taggables.tag_id')->get()->pluck('taggable_id')
                ->unique()->toArray();
            if (!empty($ids)) {
                $collection->whereIn('id', $ids);
            } else {
                return [];
            }
        }

        return ($request->has('page') && !is_null(trim($request['page'])) && $request->has('per_page') && !is_null(trim($request['per_page'])))
            ? $collection->paginate($request['per_page'])
            : $collection->paginate();
    }

    /**
     * sspfWithColumn
     *
     * @param  mixed $request
     * @param  mixed $query
     * @param  mixed $columns
     * @return void
     */
    public function sspfWithColumn($request, $query, $columns)
    {
        if ($request->has('search') && !is_null($request['search'])) {
            $query->where(function ($query) use ($request, $columns) {
                foreach ($columns as $key => $column) {
                    $query->orWhere($column, 'LIKE', "%{$request['search']}%");
                }
            });
        }

        if ($request->has('sort') && !is_null($request['sort']) && $request->has('order_by') && !is_null($request['order_by'])) {
            $sort = in_array($request['sort'], $columns) ? $request['sort'] : "id";
            $order_by = in_array($request['order_by'], ["ASC", "DESC", "asc", "desc"]) ? $request['order_by'] : "DESC";
            $query = $query->orderBy($sort, $order_by);
        }

        if ($request->has('filter') && !is_null($request['filter'])) {
            $filters = json_decode($request['filter'], true);
            $query->where(function ($query) use ($filters, $columns) {
                // Apply filter for each values
                foreach ($filters as $column => $value) {
                    if (!is_array($value)) {
                        if (in_array($column, $columns)) {
                            $new_date = [];
                            $dates = explode("to", $value);
                            $new_date[0] = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay()->toDateTimeString();
                            $new_date[1] = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay()->toDateTimeString();
                            $query->whereBetween($column, $new_date);
                        } else {
                            $query->where($column, $value);
                        }
                    } else if ($value !== "") {
                        if (in_array($column, $columns)) {
                            $query->whereIn($column, $value);
                        }
                    }
                }
            });
        }

        return ($request->has('page') && !is_null(trim($request['page'])) && $request->has('per_page') && !is_null(trim($request['per_page'])))
            ? $query->paginate($request['per_page'])
            : $query->paginate();
    }


    public function collectionSSPF($model, $collection, $request)
    {
        $model = new $model();
        $searchable = $model->sortable;
        $table = $model->getTable();
        $filterable = $model->dates;

        if ($request->get('is_light', false)) {
            return $model->select($model->light)->get();
        }

        if (!is_null($request['search'])) {
            $baseClass = class_basename($model);
            $collection = $model::whereLike($searchable, $request['search'], $baseClass);
        }

        if ($request->has('sort') && !is_null($request['sort']) && $request->has('order_by') && !is_null($request['order_by'])) {
            $order_by = in_array($request['order_by'], ["ASC", "DESC", "asc", "desc"]) ? $request['order_by'] : "DESC";
            $collection = $collection->orderBy($request['sort'], $order_by);
        }
        if ($request->has('filter') && !is_null($request['filter'])) {
            $filters = json_decode($request['filter'], true);
            $collection = $collection->where(function ($collection) use ($filters, $searchable, $filterable, $table) {
                // Apply filter for each values
                foreach ($filters as $column => $value) {
                    if (!is_array($value)) {
                        if (in_array($column, $filterable)) {
                            $new_date = [];
                            $dates = explode("to", $value);
                            $new_date[0] = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay()->toDateTimeString();
                            $new_date[1] = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay()->toDateTimeString();
                            $collection = $collection->whereBetween($table . '.' . $column, $new_date);
                        } else {
                            $collection = $collection->where($column, $value);
                        }
                    } else if ($value !== "") {
                        if (in_array($column, $searchable)) {
                            $collection = $collection->whereIn($column, $value);
                        }
                    }
                }
            });
        }
        // Tags
        $tags = json_decode($request->tags, true);
        if (!is_null($tags) && !empty($tags['tags'])) {
            $ids = Tag::whereIn("name", $tags['tags'])->join('taggables', 'tags.id', '=', 'taggables.tag_id')->get()->pluck('taggable_id')
                ->unique()->toArray();
            if (!empty($ids)) {
                $collection->whereIn('id', $ids);
            } else {
                return [];
            }
        }

        // return ($request->has('page') && !is_null(trim($request['page'])) && $request->has('per_page') && !is_null(trim($request['per_page'])))
        //     ? $collection->paginate($request['per_page'])
        //     : $collection->paginate();

        if ($request['page'] && !is_null(trim($request['page'])) && $request['per_page'] && !is_null(trim($request['per_page']))) {
            $page = $request['page'] ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $collection instanceof Collection ? $collection : Collection::make($collection);
            return new LengthAwarePaginator($items->forPage($page, $request['per_page']), $items->count(), $request['per_page'], $page, ['path' => url('admin-time-log')]);
        } else {
            $page = $request['page'] ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $collection instanceof Collection ? $collection : Collection::make($collection);
            return new LengthAwarePaginator($items->forPage('1', '2'), $items->count(), '2', '1', ['path' => url('admin-time-log')]);
        }
    }
}
