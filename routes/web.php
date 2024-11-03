<?php

use App\Livewire\CadastroUsuario;
use App\Livewire\TesteVocacional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/'); // Redireciona para a página inicial ou outra página após o logout
})->name('logout');

Route::get('/cadastro', CadastroUsuario::class)->name('cadastro-usuario');
Route::get('/teste-vocacional', TesteVocacional::class)->middleware('auth')->name('teste-vocacional');


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
