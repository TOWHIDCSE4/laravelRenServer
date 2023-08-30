<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BanklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(database_path('walletBankList.json'));
        $data = json_decode($json, true);

        foreach ($data as $user) {
            DB::table('wallet_transaction_bank_lists')->insert([
                'en_name' => $user['en_name'],
                'vn_name' => $user['vn_name'],
                'bankId' => $user['bankId'],
                'atmBin' => $user['atmBin'],
                'bank_icon' => $user['bank_icon'],
                'cardLength' => $user['cardLength'],
                'shortName' => $user['shortName'],
                'bankCode' => $user['bankCode'],
                'type' => $user['type'] ? $user['type'] : "1",
                'napasSupported' => $user['napasSupported'],
            ]);
        }
    }
}