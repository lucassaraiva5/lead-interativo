<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WebhookController;
use App\Livewire\CadastroUsuario;
use App\Livewire\TesteVocacional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::get('/webhook', [WebhookController::class, 'handle']);
Route::get('/export/excel', [ExportController::class, 'exportData']);


Route::post('/logout', function () {
    Auth::logout();
    return redirect('/'); // Redireciona para a página inicial ou outra página após o logout
})->name('logout');

Route::get('/cadastro', CadastroUsuario::class)->name('cadastro-usuario');
Route::get('/teste-vocacional', TesteVocacional::class)->middleware('auth')->name('teste-vocacional');

Auth::routes();

Route::get('/home', action: [App\Http\Controllers\HomeController::class, 'index'])->name('home');
