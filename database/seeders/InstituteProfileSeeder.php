<?php

namespace Database\Seeders;

use App\Enums\InstituteType;
use App\Models\Setting;
use App\Support\InstituteProfile;
use Illuminate\Database\Seeder;

class InstituteProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::query()->where('key', InstituteProfile::SETTING_KEY)->exists()) {
            return;
        }

        $configured = (string) config('institute.type', InstituteType::School->value);
        $type = InstituteType::tryFrom($configured) ?? InstituteType::School;

        InstituteProfile::setType($type);
    }
}
