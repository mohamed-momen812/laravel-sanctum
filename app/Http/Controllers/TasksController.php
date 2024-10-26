<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TasksResource;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TasksResource::collection(...): This wraps the resulting Task collection in a resource collection. In Laravel, a resource collection is typically used to format the output for APIs consistently. The TasksResource class would define how each Task and its related user data should be formatted when returned as JSON, making the API response more structured and customized.
        // using success to handle the full response, using TaskResource to handle data from collection
        return $this->success(TasksResource::collection(
                Task::with('user')->where('user_id', Auth::user()->id)->get()
        ), 'custome message');


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);
        return new TasksResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // here your can't handle error because laravel ModelNotFoundException will throw before reach show function because using modle binding route
        // so error handling is inside Handler in Exceptions folder

        // if(!$task) {
        //     return $this->error('', 'No task with this id', 404);
        // }

        // each user only has access to its tasks
        if(Auth::user()->id !== $task->user->id){
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        return new TasksResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if(Auth::user()->id !== $task->user->id){
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        $task->update($request->all());

        return new TasksResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if(Auth::user()->id !== $task->user->id){
            return $this->error('', 'You are not authorized to make this request', 403);
        }

        $task->delete();

        return $this->success('', 'Task has been deleted successfully', 204);
    }


}
