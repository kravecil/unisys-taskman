<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectController;

use App\Http\Controllers\DocumentController;
// use App\Http\Controllers\OutgoingDocumentController;
use App\Http\Controllers\ReportController;

Route::middleware('auth:sanctum')->group(function() {
    Route::get('counters', [ TaskController::class, 'counters' ]);

    Route::get('/users', [UserController::class, 'index']);

    Route::prefix('tasks')->group(function() {
        Route::get('', [ TaskController::class, 'index' ]);
        Route::post('', [ TaskController::class, 'store' ]);
        // Route::get('/all', [ TaskController::class, 'indexAll' ]);
        Route::get('/report', [ TaskController::class, 'report' ]);
        
        Route::get('{task}', [ TaskController::class, 'show' ]);
        Route::put('{task}/users', [ TaskController::class, 'updateUsers' ]);
        Route::put('{task}/deadline', [ TaskController::class, 'updateDeadline' ]);
        Route::delete('{task}', [ TaskController::class, 'destroy' ]);

        Route::get('{task}/histories', [ HistoryController::class, 'index' ]);
        Route::get('{task}/comments', [ CommentController::class, 'index' ]);
        Route::get('{task}/attachments/{attachment}', [ AttachmentController::class, 'show' ]);

        Route::post('{task}/complete', [ TaskController::class, 'complete']);
        Route::post('{task}/back', [ TaskController::class, 'back']);
        Route::post('{task}/comment', [ TaskController::class, 'comment']);
        Route::delete('{task}/close', [ TaskController::class, 'close']);
        Route::post('{task}/attach/{project?}', [ TaskController::class, 'attachProject' ]);
        
        Route::post('/{task}/update-importance', [ TaskController::class, 'updateImportance' ]);
        Route::post('/{task}/repeat', [ TaskController::class, 'setRepeat' ]);
    });

    Route::prefix('/documents')->group(function() {
        Route::get('/list-sent-by', [ DocumentController::class, 'indexSentBy' ]);
        Route::get('/list-partners', [ DocumentController::class, 'indexPartners' ]);
        Route::get('/list-kinds', [ DocumentController::class, 'indexKinds' ]);
        Route::get('/list-signers', [ DocumentController::class, 'indexSigners' ]);
        Route::get('/types', function(Request $request) { return \App\Models\DocumentType::all(); });
        Route::get('/last-number', [ DocumentController::class, 'getLastNumber' ]);
        Route::get('/last-inner-number', [ DocumentController::class, 'getLastInnerNumber' ]);
        Route::get('/', [ DocumentController::class, 'index' ]);
        Route::post('/', [ DocumentController::class, 'store' ]);
        Route::get('/{document}', [ DocumentController::class, 'show' ]);
        Route::delete('/{document}', [ DocumentController::class, 'destroy' ]);
        Route::post('/{document}/attachments', [ AttachmentController::class, 'store' ]);
        Route::get('/{document}/attachments/{attachment}', [ AttachmentController::class, 'showDocument' ]);
        Route::delete('/{document}/attachments/{attachment}', [ AttachmentController::class, 'destroy' ]);
        Route::post('/{document}/read', [ DocumentController::class, 'read']);
        Route::post('/{document}/approve', [ DocumentController::class, 'approve']);
        Route::post('/{document}/attach', [ DocumentController::class, 'attachTask']);
        Route::post('/{document}/update-number', [ DocumentController::class, 'updateNumber' ]);
        Route::post('/{document}/update-issued-at', [ DocumentController::class, 'updateIssuedAt' ]);
        Route::post('/{document}/update-sent', [ DocumentController::class, 'updateSent' ]);
        Route::post('/{document}/update-mailing-users', [ DocumentController::class, 'updateMailingUsers' ]);
        Route::post('/{document}/update-fields', [ DocumentController::class, 'updateFields' ]);
    });

    Route::prefix('/projects')->group(function() {
        Route::get('/', [ ProjectController::class, 'index' ]);
        Route::post('/', [ ProjectController::class, 'store' ]);
        Route::get('/{project}', [ ProjectController::class, 'show' ]);
        Route::get('/{project}/tasks', [ ProjectController::class, 'showTasks' ]);
        Route::delete('/{project}', [ ProjectController::class, 'destroy' ]);
    });

    Route::prefix('/partners')->group(function() {
        Route::get('/', [ PartnerController:: class, 'index']);
        Route::put('/', [ PartnerController:: class, 'store']);
        Route::post('/{partner}', [ PartnerController:: class, 'update']);
    });

    Route::prefix('/report')->group(function() {
        Route::get('/tasks', [ ReportController:: class, 'tasks']);
    });

    // Route::prefix('/outgoing-documents')->group(function() {
    //     Route::get('/receivers', [ OutgoingDocumentController::class, 'indexReceivers' ]);
    //     Route::get('/last-number', [ OutgoingDocumentController::class, 'getLastNumber' ]);

    //     Route::get('/', [ OutgoingDocumentController::class, 'index' ]);
    //     Route::put('/', [ OutgoingDocumentController::class, 'store' ]);
    //     Route::delete('/{outgoingDocument}', [ OutgoingDocumentController::class, 'destroy' ]);
    // });

    /** TEMP */
    // Route::get('/test', function(Request $request) {
    //     // $user = $request->user();
        
    //     // return $user->documents()->union($user->documentsIssued())->get();
    //     $result = [];
    //     \App\Models\Task::lazy()
    //     ->whereNull('closed_at')
    //     ->each(function($item, $key) use (&$result){
    //         $result[] = $item;
    //     });
    //     return $result;
    // });
});
