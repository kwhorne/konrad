<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContractFileUpload extends Component
{
    use WithFileUploads;

    #[Validate(['attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'])]
    public $attachments = [];

    public function removeAttachment($index): void
    {
        $attachment = $this->attachments[$index];
        $attachment->delete();
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function getAttachmentPaths()
    {
        return array_map(function ($attachment) {
            return $attachment->getFilename();
        }, $this->attachments);
    }

    public function render()
    {
        return view('livewire.contract-file-upload');
    }
}
