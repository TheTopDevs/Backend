<?php

namespace App\Http\Traits;

use Illuminate\Validation\Rule;

trait RequestPerPagePagination
{
    public array $perPageList = [15, 20, 25, 50];
    public int $defaultPerPage = 15;

    public function perPage(): int
    {
        return $this->input('perPage', $this->defaultPerPage);
    }
}
