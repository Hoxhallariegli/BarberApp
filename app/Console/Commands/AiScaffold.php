<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AiScaffold extends Command
{
    protected $signature = 'ai:scaffold {json}';
    protected $description = 'Non-interactive scaffolding for AI mode';

    public function handle()
    {
        $data = json_decode($this->argument('json'), true);
        if (!$data) {
            $this->error('Invalid JSON input.');
            return;
        }

        $model = $data['model'];
        $fields = $data['fields'];
        $withApi = $data['api'] ? '--api' : '';
        $withFirebase = $data['firebase'] ? '--firebase' : '';

        // Build the interactive input string for the pipe simulation
        // name\n0\nno\nslug\n0\nno\n\nicon
        $input = "";
        foreach ($fields as $field) {
            $input .= $field['name'] . "\n";
            // Map types to indices if needed, or use the type name if the command accepts it
            // NewView uses choice(), which accepts the value.
            $input .= $field['type'] . "\n";

            if ($field['type'] === 'foreignId') {
                $input .= ($field['constrained'] ?? "") . "\n";
            }

            if ($field['type'] === 'enum') {
                $input .= implode(',', $field['options'] ?? []) . "\n";
            }

            $input .= ($field['nullable'] ? "yes" : "no") . "\n";
        }

        $input .= "\n"; // Empty field name to finish
        $input .= ($data['icon'] ?? "chevron-right") . "\n";

        // These are for the confirm() calls at the end of the loop in NewView if they exist
        // But NewView has those as options or choices now.

        // The most reliable way is to actually move the logic from NewView to a Service.
        // But for now, let's try to call NewView with the data.

        // Actually, let's just use the logic from NewView directly here to be 100% sure it works.
        // But that's a lot of duplication.

        $this->info("Executing NewView for $model...");

        // We'll use a hack to provide interactive input to a command
        // Since we are in a command ourselves, we can't easily pipe.

        $this->warn("AI Scaffolding is being prepared. Please use the terminal command provided in the UI for now.");
    }
}
