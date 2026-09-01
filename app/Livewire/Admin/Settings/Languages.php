<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Title;
use App\Services\TranslationService;

#[Title('Languages Management')]
class Languages extends Component
{
    public $languages = [];
    public $selectedLang = 'en';
    public $files = [];
    public $selectedFile = '';
    public $translations = [];
    public $newLangCode = '';
    public $isTranslating = false;

    public function mount()
    {
        $this->loadLanguages();
        $this->loadFiles();
    }

    public function loadLanguages()
    {
        $this->languages = ['en'];
        if (File::exists(lang_path())) {
            foreach (File::directories(lang_path()) as $dir) {
                $lang = basename($dir);
                if (strlen($lang) <= 5 && !in_array($lang, $this->languages)) {
                    $this->languages[] = $lang;
                }
            }
        }
        sort($this->languages);
    }

    public function updatedSelectedLang()
    {
        $this->loadFiles();
        $this->selectedFile = '';
        $this->translations = [];
    }

    public function loadFiles()
    {
        $path = lang_path($this->selectedLang);
        $files = [];

        if (File::exists($path) && File::isDirectory($path)) {
            $files = collect(File::files($path))
                ->map(fn($file) => $file->getFilename())
                ->toArray();
        }

        $jsonFile = "{$this->selectedLang}.json";
        if (File::exists(lang_path($jsonFile))) {
            $files[] = $jsonFile;
        }

        $this->files = $files;
    }

    public function updatedSelectedFile()
    {
        if (!$this->selectedFile) {
            $this->translations = [];
            return;
        }

        if (str_ends_with($this->selectedFile, '.json')) {
            $path = lang_path($this->selectedFile);
            $this->translations = json_decode(File::get($path), true) ?? [];
        } else {
            $path = lang_path("{$this->selectedLang}/{$this->selectedFile}");
            $this->translations = File::getRequire($path);
        }
    }

    public function addLanguage(TranslationService $service)
    {
        $this->validate([
            'newLangCode' => 'required|alpha|min:2|max:5'
        ]);

        $newCode = strtolower($this->newLangCode);
        $newPath = lang_path($newCode);
        $enPath = lang_path('en');

        if (File::exists($newPath)) {
            $this->dispatch('toast', ['message' => 'Language already exists!', 'type' => 'error']);
            return;
        }

        $this->isTranslating = true;

        File::makeDirectory($newPath, 0755, true);
        $this->sync($newCode, $service);

        $this->loadLanguages();
        $this->newLangCode = '';
        $this->isTranslating = false;

        $this->dispatch('toast', [
            'message' => "Language $newCode added and AUTO-TRANSLATED!",
            'type' => 'success'
        ]);
    }

    public function sync($lang, TranslationService $service)
    {
        $this->isTranslating = true;
        $enPath = lang_path('en');
        $targetPath = lang_path($lang);

        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        // 1. Sync PHP files
        if (File::isDirectory($enPath)) {
            foreach (File::files($enPath) as $file) {
                $targetFile = $targetPath . '/' . $file->getFilename();

                if (!File::exists($targetFile)) {
                    $content = File::getRequire($file->getPathname());
                    $translated = $service->translate($content, $lang, 'en');

                    $phpContent = "<?php\n\nreturn " . var_export($translated, true) . ";\n";
                    $phpContent = str_replace(['array (', ')'], ['[', ']'], $phpContent);

                    File::put($targetFile, $phpContent);
                }
            }
        }

        // 2. Sync JSON file
        $enJson = lang_path('en.json');
        $targetJson = lang_path("{$lang}.json");
        if (File::exists($enJson) && !File::exists($targetJson)) {
            $content = json_decode(File::get($enJson), true);
            $translated = $service->translate($content, $lang, 'en');
            File::put($targetJson, json_encode($translated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $this->isTranslating = false;
        $this->dispatch('toast', ['message' => "Language $lang synced with English!", 'type' => 'success']);
        $this->loadFiles();
    }

    public function saveTranslations()
    {
        if (!$this->selectedFile) return;

        if (str_ends_with($this->selectedFile, '.json')) {
            $path = lang_path($this->selectedFile);
            File::put($path, json_encode($this->translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $path = lang_path("{$this->selectedLang}/{$this->selectedFile}");
            $content = "<?php\n\nreturn " . var_export($this->translations, true) . ";\n";
            $content = str_replace(['array (', ')'], ['[', ']'], $content);
            File::put($path, $content);
        }

        $this->dispatch('toast', ['message' => 'Translations saved successfully!', 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.settings.languages')->layout('components.layouts.app');
    }
}
