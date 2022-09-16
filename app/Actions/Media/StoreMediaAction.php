<?php

namespace App\Actions\Media;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreMediaAction
{
  private UploadedFile $file;

  public function handle(Request $request): Media
  {
    $this->file = $request->file;

    $file_info = $this->_getFileInfo();
    $saved_path = $this->_storeFile($file_info);

    return Media::create([...$file_info, 'description' => $request->description, 'path' => $saved_path]);
  }

  private function _getFileInfo(): array
  {
    $filename = $this->file->hashName();
    $extension = $this->file->extension();
    $type = $this->_getType($this->file->getMimeType());

    return [
      'type' => $type,
      'extension' => $extension,
      'filename' => $filename,
      'size_kb' => $this->file->getSize(),
    ];
  }

  /**
   * @param  string  $mime_type
   * @return string Either images or videos
   */
  private function _getType(string $mime_type): string
  {
    return Str::of(explode('/', $mime_type)[0])->toString();
  }

  private function _storeFile(array $file_info): string
  {
    $type = $file_info['type'];
    return Storage::putFileAs($type, $this->file, $file_info['filename']);
  }
}
