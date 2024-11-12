<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserQuestionStatus;
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
        $users = UserQuestionStatus::all();

        // Verifica se existem usuários
        if ($users->isEmpty()) {
            $this->info('No users found.');
            return;
        }

        // Exibe os usuários em uma tabela
        $this->table(
            ['ID', 'Instagram', 'School', 'Image Sent', 'Image Generated', 'Created At'],
            $users->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'instagram' => $user->instagram,
                    'School' => $user->school,
                    'Image Sent' => $user->image_sent,
                    'Image Generated' => $user->image_generated,
                    'Created At' => $user->created_at->toDateTimeString(),
                ];
            })->toArray()
        );
    }
}
