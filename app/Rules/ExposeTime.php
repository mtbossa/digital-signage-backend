<?php

namespace App\Rules;

use App\Models\Media;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ExposeTime implements InvokableRule, DataAwareRule
{
    public bool $implicit = true;

    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if (! array_key_exists('media_id', $this->data)) {
            return;
        }
        if (is_null($this->data['media_id'])) {
            return;
        }

        $media = Media::find($this->data['media_id']);
        if ($media->type === 'video' && $value !== null) {
            $fail('The :attribute must be null when media is a video.');

            return;
        }

        if ($media->type === 'image' && $value === null) {
            $fail('The :attribute must not be null when media is an image.');
        }
    }

    public function setData($data): ExposeTime|static
    {
        $this->data = $data;

        return $this;
    }
}
