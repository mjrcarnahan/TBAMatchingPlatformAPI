<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('memberships')->insert([
            ['id' => 1, 'name' => 'free'],
            ['id' => 2, 'name' => 'pro']
        ]);

        $profiles = Profile::all();

        foreach($profiles as $profile){
            $profile->membership_id = 1;
            $profile->save();
        }

    }
}
