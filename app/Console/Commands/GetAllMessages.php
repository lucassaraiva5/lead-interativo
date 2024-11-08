<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class GetAllMessages extends Command
{
    protected $signature = 'messages:list';

    // A descrição do comando
    protected $description = 'List all users from the database';

    /**
     * Execute o comando no terminal.
     *
     * @return void
     */

    public function handle()
    {
        $messages = Message::all();

        if ($messages->isEmpty()) {
            $this->info('No messages found.');
            return;
        }

        foreach ($messages as $message) {
            $this->line("From: {$message->from} | To: {$message->to} | Message: {$message->body} | Received at: {$message->received_at}");
        }

        $this->info("Total messages: " . $messages->count());
    }
}
