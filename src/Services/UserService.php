<?php

namespace Chronologue\Security\Services;

use Chronologue\Core\Contracts\SearchParams;
use Chronologue\Core\Database\Support\LikeSearch;
use Chronologue\Core\Database\Support\OrderByKey;
use Chronologue\Core\Database\Support\PaginateQuery;
use Chronologue\Core\Support\Service;
use Chronologue\Security\Database\Eloquent\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserService extends Service
{
    public function get(SearchParams $params): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($search)) {
            $query->where(function (Builder $query) use ($search) {
                $query->tap(new LikeSearch('name', $search));
                $query->tap(new LikeSearch('email', $search, 'or'));
            });
        }

        $query->tap(new OrderByKey());

        return $query->pipe(new PaginateQuery($params));
    }
}