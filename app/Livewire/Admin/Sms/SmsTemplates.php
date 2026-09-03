<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Sms;

use App\Models\SmsTemplate;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SmsTemplates extends Component
{
    public bool $showingModal = false;
    public ?int $templateId = null;

    public string $type = '';
    public array $body = [
        'sq' => '',
        'en' => '',
    ];
    public bool $is_active = true;

    protected $rules = [
        'type' => 'required|string|max:255',
        'body.sq' => 'required|string',
        'body.en' => 'required|string',
        'is_active' => 'boolean',
    ];

    public function render(): View
    {
        return view('livewire.admin.sms.sms-templates', [
            'templates' => SmsTemplate::all(),
        ])->layout('components.layouts.app');
    }

    public function create(): void
    {
        $this->reset(['type', 'templateId']);
        $this->body = ['sq' => '', 'en' => ''];
        $this->is_active = true;
        $this->showingModal = true;
    }

    public function edit(int $id): void
    {
        $template = SmsTemplate::findOrFail($id);
        $this->templateId = $id;
        $this->type = $template->type;
        $this->body = array_merge(['sq' => '', 'en' => ''], (array) $template->body);
        $this->is_active = $template->is_active;
        $this->showingModal = true;
    }

    public function save(): void
    {
        $this->validate();

        SmsTemplate::updateOrCreate(
            ['id' => $this->templateId],
            [
                'type' => $this->type,
                'body' => $this->body,
                'is_active' => $this->is_active,
            ]
        );

        $this->showingModal = false;
        $this->dispatch('toast', ['message' => __('Template saved successfully!'), 'type' => 'success']);
    }

    public function delete(int $id): void
    {
        SmsTemplate::findOrFail($id)->delete();
        $this->dispatch('toast', ['message' => __('Template deleted.'), 'type' => 'success']);
    }
}
