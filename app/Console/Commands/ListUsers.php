<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    protected $signature = 'users:list';

    // A descrição do comando
    protected $description = 'List all users from the database';

    /**
     * Execute o comando no terminal.
     *
     * @return void
     */
    public function handle()
    {
        // Obtém todos os usuários
        $users = User::all();

        // Verifica se existem usuários
        if ($users->isEmpty()) {
            $this->info('No users found.');
            return;
        }

        // Exibe os usuários em uma tabela
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $users->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Photo' => $user->photo,
                    'Created At' => $user->created_at->toDateTimeString(),
                ];
            })->toArray()
        );
    }
}
