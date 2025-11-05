<?php

namespace App\Exports;

use App\Models\PostLog;
use Maatwebsite\Excel\Concerns\FromCollection;

class PostLogsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return PostLog::select('platform', 'username', 'message', 'status', 'post_url', 'created_at')->get();
    }

    public function headings(): array
    {
        return ['Platform', 'Username', 'Message', 'Status', 'Post URL', 'Created At'];
    }
}

