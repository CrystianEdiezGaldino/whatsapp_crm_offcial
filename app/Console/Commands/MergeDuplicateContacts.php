<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Support\PhoneNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class MergeDuplicateContacts extends Command
{
    protected $signature = 'contacts:merge-duplicates';

    protected $description = 'Unifica contatos com o mesmo telefone (variantes BR com/sem 9)';

    public function handle(): int
    {
        $groups = Contact::all()->groupBy(fn (Contact $c) => PhoneNormalizer::forApi($c->phone));
        $merged = 0;

        foreach ($groups as $contacts) {
            if ($contacts->count() < 2) {
                continue;
            }

            /** @var Collection<int, Contact> $contacts */
            $primary = $contacts->sortByDesc(fn (Contact $c) => $c->last_message_at?->timestamp ?? 0)
                ->sortByDesc('id')
                ->first();
            $apiPhone = PhoneNormalizer::forApi($primary->phone);

            foreach ($contacts->where('id', '!=', $primary->id) as $duplicate) {
                foreach ($duplicate->conversations as $conv) {
                    $target = Conversation::firstOrCreate(
                        ['contact_id' => $primary->id, 'status' => 'open'],
                        ['last_message_at' => $conv->last_message_at ?? now()]
                    );

                    Message::where('conversation_id', $conv->id)->update(['conversation_id' => $target->id]);

                    if ($conv->id !== $target->id) {
                        $conv->update(['status' => 'closed']);
                    }
                }

                $duplicate->delete();
                $merged++;
            }

            $primary->update(['phone' => $apiPhone]);
        }

        $this->info("Contatos duplicados unificados: {$merged}");

        return self::SUCCESS;
    }
}
