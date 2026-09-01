<?php

namespace App\Livewire\Admin;

use App\Services\GroqService;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('AI Assistant')]
class AiAssistant extends Component
{
    public string $prompt = '';
    public bool $isProcessing = false;
    public array $history = [];

    public function process()
    {
        $this->validate(['prompt' => 'required|min:3']);
        $this->isProcessing = true;

        try {
            $ai = new GroqService();
            $actions = $ai->interpretPrompt($this->prompt);

            $results = [];
            foreach ($actions as $action) {
                if (!isset($action['model'])) continue;

                $results[] = [
                    'model' => $action['model'],
                    'intent' => $action['intent'] ?? 'create',
                    'command' => $this->generateCommand($action)
                ];
            }

            $this->history[] = [
                'type' => 'success',
                'message' => "I have processed your request.",
                'results' => $results
            ];

            $this->prompt = '';
        } catch (\Exception $e) {
            $this->history[] = ['type' => 'error', 'message' => $e->getMessage()];
        }

        $this->isProcessing = false;
    }

    protected function generateCommand(array $data): string
    {
        $intent = $data['intent'] ?? 'create';
        $model = $data['model'];

        if ($intent === 'delete') {
            return "php artisan remove:view $model";
        }

        // Create logic (piped)
        $input = "";
        $fields = $data['fields'] ?? [];
        $allowedTypes = ['string', 'text', 'integer', 'bigInteger', 'boolean', 'decimal', 'date', 'datetime', 'foreignId', 'enum'];

        foreach ($fields as $field) {
            $type = strtolower($field['type'] ?? 'string');
            $type = in_array($type, $allowedTypes) ? $type : 'string';

            $input .= $field['name'] . "`n";
            $input .= $type . "`n";
            if ($field['type'] === 'foreignId') {
                $input .= ($field['constrained'] ?? "users") . "`n";
            }
            if ($field['type'] === 'enum') {
                $input .= implode(',', $field['options'] ?? ['default']) . "`n";
            }
            $input .= ($field['nullable'] ? "yes" : "no") . "`n";
        }

        $input .= "`n"; // Finish loop

        $icon = str_replace(['heroicon-o-', 'heroicon-'], '', $data['icon'] ?? 'chevron-right');

        // Surgical mapping for common Heroicon v1 vs v2 errors
        $iconMap = [
            'file' => 'document',
            'book' => 'book-open',
            'office-building' => 'building-office',
            'desktop' => 'computer-desktop',
            'clipboard' => 'clipboard-document',
            'mail' => 'envelope',
            'box' => 'archive-box',
            'pencil' => 'pencil-square',
        ];

        $icon = $iconMap[$icon] ?? $icon;

        $input .= $icon;

        $flags = ($data['api'] ?? false) ? " --api" : "";
        return "\"$input\" | php artisan new:view $model$flags";
    }

    public function render()
    {
        return view('livewire.admin.ai-assistant')->layout('components.layouts.app');
    }
}
