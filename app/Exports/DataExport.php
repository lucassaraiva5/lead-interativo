<?php

namespace App\Exports;

use App\Models\UserQuestionStatus;
use Maatwebsite\Excel\Concerns\FromCollection;

class DataExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UserQuestionStatus::all();
    }
}
