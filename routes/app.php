<?php


use Illuminate\Support\Facades\Route;



Route::middleware('auth')->group(function () {
	Route::get('/dashboard', fn () => \Inertia\Inertia::render('Dashboard'))->name('dashboard');
	Route::get('/profile', fn () => \Inertia\Inertia::render('Profile'))->name('profile');
	
});