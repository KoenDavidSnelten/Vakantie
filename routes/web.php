<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\VacationDatePlannerController;
use App\Http\Controllers\VacationHotelCommentController;
use App\Http\Controllers\VacationHotelController;
use App\Http\Controllers\VacationPackingListController;
use App\Http\Controllers\VacationSkiAreaCommentController;
use App\Http\Controllers\VacationSkiAreaController;
use App\Http\Controllers\VacationTravelOptionController;
use App\Http\Controllers\VacationTravelPlannerController;
use App\Http\Controllers\VacationVehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/games', function () {
    return view('games.index');
})->middleware(['auth', 'verified'])->name('games.index');

Route::get('/games/boom-it', function () {
    return view('games.boom-it');
})->middleware(['auth', 'verified'])->name('games.boom-it');

Route::get('/games/picolo', function () {
    return view('games.picolo');
})->middleware(['auth', 'verified'])->name('games.picolo');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vacations', [VacationController::class, 'index'])->name('vacations.index');
    Route::get('/vacations/create', [VacationController::class, 'create'])->name('vacations.create');
    Route::post('/vacations', [VacationController::class, 'store'])->name('vacations.store');
    Route::get('/vacations/{vacation}', [VacationController::class, 'show'])->name('vacations.show');
    Route::get('/vacations/{vacation}/edit', [VacationController::class, 'edit'])->name('vacations.edit');
    Route::patch('/vacations/{vacation}', [VacationController::class, 'update'])->name('vacations.update');

    Route::get('/vacations/{vacation}/date-planner', [VacationDatePlannerController::class, 'show'])->name('vacations.date-planner');
    Route::patch('/vacations/{vacation}/date-planner/range', [VacationDatePlannerController::class, 'updateRange'])->name('vacations.date-planner.range');
    Route::put('/vacations/{vacation}/date-planner/availability', [VacationDatePlannerController::class, 'upsertAvailability'])->name('vacations.date-planner.availability');
    Route::patch('/vacations/{vacation}/date-planner/final', [VacationDatePlannerController::class, 'finalize'])->name('vacations.date-planner.final');

    Route::get('/vacations/{vacation}/locations', [VacationSkiAreaController::class, 'index'])->name('vacations.locations.index');
    Route::post('/vacations/{vacation}/ski-areas', [VacationSkiAreaController::class, 'store'])->name('vacations.ski-areas.store');
    Route::patch('/vacations/{vacation}/ski-areas/{skiArea}', [VacationSkiAreaController::class, 'update'])->name('vacations.ski-areas.update');
    Route::delete('/vacations/{vacation}/ski-areas/{skiArea}', [VacationSkiAreaController::class, 'destroy'])->name('vacations.ski-areas.destroy');
    Route::post('/vacations/{vacation}/ski-areas/{skiArea}/vote', [VacationSkiAreaController::class, 'vote'])->name('vacations.ski-areas.vote');
    Route::post('/vacations/{vacation}/ski-areas/{skiArea}/comments', [VacationSkiAreaCommentController::class, 'store'])->name('vacations.ski-areas.comments.store');
    Route::delete('/vacations/{vacation}/ski-areas/{skiArea}/comments/{comment}', [VacationSkiAreaCommentController::class, 'destroy'])->name('vacations.ski-areas.comments.destroy');

    Route::post('/vacations/{vacation}/ski-areas/{skiArea}/hotels', [VacationHotelController::class, 'store'])->name('vacations.hotels.store');
    Route::patch('/vacations/{vacation}/ski-areas/{skiArea}/hotels/{hotel}', [VacationHotelController::class, 'update'])->name('vacations.hotels.update');
    Route::delete('/vacations/{vacation}/ski-areas/{skiArea}/hotels/{hotel}', [VacationHotelController::class, 'destroy'])->name('vacations.hotels.destroy');
    Route::post('/vacations/{vacation}/ski-areas/{skiArea}/hotels/{hotel}/vote', [VacationHotelController::class, 'vote'])->name('vacations.hotels.vote');
    Route::post('/vacations/{vacation}/ski-areas/{skiArea}/hotels/{hotel}/comments', [VacationHotelCommentController::class, 'store'])->name('vacations.hotels.comments.store');
    Route::delete('/vacations/{vacation}/ski-areas/{skiArea}/hotels/{hotel}/comments/{comment}', [VacationHotelCommentController::class, 'destroy'])->name('vacations.hotels.comments.destroy');

    Route::get('/vacations/{vacation}/travel-planner', [VacationTravelPlannerController::class, 'show'])->name('vacations.travel-planner');

    Route::post('/vacations/{vacation}/vehicles', [VacationVehicleController::class, 'store'])->name('vacations.vehicles.store');
    Route::patch('/vacations/{vacation}/vehicles/{vehicle}', [VacationVehicleController::class, 'update'])->name('vacations.vehicles.update');
    Route::delete('/vacations/{vacation}/vehicles/{vehicle}', [VacationVehicleController::class, 'destroy'])->name('vacations.vehicles.destroy');

    Route::post('/vacations/{vacation}/travel-options', [VacationTravelOptionController::class, 'store'])->name('vacations.travel-options.store');
    Route::patch('/vacations/{vacation}/travel-options/{travelOption}', [VacationTravelOptionController::class, 'update'])->name('vacations.travel-options.update');
    Route::delete('/vacations/{vacation}/travel-options/{travelOption}', [VacationTravelOptionController::class, 'destroy'])->name('vacations.travel-options.destroy');

    Route::get('/vacations/{vacation}/packing-list', [VacationPackingListController::class, 'index'])->name('vacations.packing-list');
    Route::post('/vacations/{vacation}/packing-list', [VacationPackingListController::class, 'store'])->name('vacations.packing-list.store');
    Route::delete('/vacations/{vacation}/packing-list/{item}', [VacationPackingListController::class, 'destroy'])->name('vacations.packing-list.destroy');
    Route::post('/vacations/{vacation}/packing-list/{item}/toggle', [VacationPackingListController::class, 'toggle'])->name('vacations.packing-list.toggle');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
