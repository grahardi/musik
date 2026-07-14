<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run()
    {
        $genres = [
            'Pop', 'Pop Melayu', 'Dangdut', 'Religi', 'Rohani',
            'Rock', 'Ska / Reggae', 'Akustik', 'Campursari',
            'Anak-anak', 'Nasional', 'Jazz', 'Indie / Folk',
        ];

        foreach ($genres as $name) {
            Genre::firstOrCreate(['name' => $name]);
        }
    }
}
