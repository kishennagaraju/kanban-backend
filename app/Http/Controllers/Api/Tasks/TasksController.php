<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Column;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardsRequest;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MySql;

class TasksController extends Controller
{
    private $_dumper;

    public function __construct()
    {
        if (!$this->_dumper) {
            $this->_dumper = MySql::create()
                                ->setDbName(env('DB_DATABASE'))
                                ->setUserName(env('DB_USERNAME'))
                                ->setPassword(env('DB_PASSWORD'));
        }
    }

    public function list(Request $request)
    {
        $query = Column::with(['tasks' => function ($taskQuery) use ($request) {
            $taskQuery->where('status', '=', $request->query('status', '1'));
            if ($request->query('date')) {
                $taskQuery->where('created_at', '=', date('Y-m-d', strtotime($request->query('date'))));
            }
            $taskQuery->orderBy('priority');
        }]);
        if ($request->query('date')) {
            $query->where('created_at', '=', date('Y-m-d', strtotime($request->query('date'))));
        }

        $results = $query->orderBy('priority')
        ->get()
        ->toArray();

        return response()->json($results)->setStatusCode(200);
    }

    public function save(CardsRequest $request)
    {
        $data = $request->post('data');
	$data = json_decode($data, true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $columnPriority = 1;
        foreach ($data as $column) {
            if (Column::query()->where('id', '=', $column['id'])->exists()) {
                Column::query()->where('id', '=', $column['id'])->delete();
            }

            Column::query()->create([
                'id' => $column['id'],
                'title' => $column['title'],
                'priority' => $columnPriority,
            ]);


            if ($column['tasks']) {
                $taskPriority = 1;
                foreach ($column['tasks'] as $task) {
                    if (Task::query()->where('id', '=', $task['id'])->exists()) {
                        Task::query()->where('id', '=', $task['id'])->delete();
                    }
                    Task::query()->create([
                        'id' => $task['id'],
                        'column_id' => $column['id'],
                        'title' => $task['title'],
                        'status' => $task['status'],
                        'priority' => $taskPriority,
                    ]);
                    $taskPriority++;
                }
            }
            $columnPriority++;
        }

	DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json($data)->setStatusCode(200);
    }

    public function exportDb()
    {
        $filePath = storage_path('app/public') . "/database_dump.sql";
        $this->_dumper->dumpToFile($filePath);

        return response()->download($filePath, 'database_dump.sql', [], 'attachment');
    }
}
