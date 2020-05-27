<?php

namespace app\http\middleware;


use app\common\model\Task;

class CheckTaskRunKey
{
    public function handle($request, \Closure $next)
    {
        $key = input('run');
        $task = Task::where('status', 'neq', '已完成')->select();
        if (!$task) return json(['code' => -1, 'msg' => 'No tasks to run']);
        if ($key != 'mitomos2020') return json(['code' => -1, 'msg' => 'Incorrect key']);
        return $next($request);
    }
}