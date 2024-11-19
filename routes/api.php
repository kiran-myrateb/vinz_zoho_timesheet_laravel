<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;

// Define a route for fetching all tasks
Route::get('tasks', [TaskController::class, 'index']);

// Define a route for fetching a single task by ID
Route::get('tasks/{id}', [TaskController::class, 'show']);

// Define a route for creating a new task
Route::post('tasks', [TaskController::class, 'store']);

// Define a route for updating an existing task
Route::put('tasks/{id}', [TaskController::class, 'update']);

// Define a route for deleting a task
Route::delete('tasks/{id}', [TaskController::class, 'destroy']);


Route::post('timesheet', [TaskController::class, 'timesheetData']);

Route::post('timesheet-details', [TaskController::class, 'timesheetDetailsData']);

Route::post('refreshtoken', [TaskController::class, 'refereshzohotoken']);

