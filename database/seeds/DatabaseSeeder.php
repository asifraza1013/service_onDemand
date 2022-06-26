<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run()
    {
        // $this->call(UsersTableSeeder::class);

         $permissions = [
//            'ticket-list',
//            'ticket-view',
//            'ticket-delete',
//            'slider-list',
//            'slider-edit',
//            'slider-delete',
         ];
         
       foreach ($permissions as $permission){
           \Spatie\Permission\Models\Permission::where(['name' => $permission])->delete();
           \Spatie\Permission\Models\Permission::create(['name' => $permission,'guard_name' => 'admin']);
       }
    }
}
