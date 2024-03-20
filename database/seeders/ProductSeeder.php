<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */

    public function run(): void
    {
        DB::table('products')->insert([
            'title' => 'Product',
            'artist' => 'Artist', // Set email as verified immediately
            'height' => 3245,
            'width' => 1345,
            'description' => Str::random(60), // Generate a random remember token
            'price' => 25.55, // Set created_at to current time
            'image_url' => 'https://media.discordapp.net/attachments/1207515376070434836/1212078583770513488/IMG_1379.png?ex=65f08725&is=65de1225&hm=d90384a85db91d85ac5e0792d850efcd78bf13020a8020026b4e35058bcefc7a&=&format=webp&quality=lossless&width=409&height=671', // Set updated_at to current time
            'category_id' => 2
        ]);
    }
}