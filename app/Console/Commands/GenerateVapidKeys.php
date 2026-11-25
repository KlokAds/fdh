<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

#[Signature('fdh:vapid')]
#[Description('Generate the VAPID key pair for browser push notifications. Add the printed lines to .env once; changing them later disconnects every subscribed device.')]
class GenerateVapidKeys extends Command
{
    public function handle(): int
    {
        if (config('fdh.vapid.public_key')) {
            $this->warn('VAPID keys already exist in .env. Replacing them will disconnect every device that turned on notifications.');

            if (! $this->confirm('Generate new keys anyway?')) {
                return self::SUCCESS;
            }
        }

        try {
            $keys = VAPID::createVapidKeys();
        } catch (\RuntimeException $e) {
            $this->error('OpenSSL could not create the key ('.$e->getMessage().').');
            $this->line('On Windows, set OPENSSL_CONF to an openssl.cnf file and run again. Linux servers work out of the box.');

            return self::FAILURE;
        }

        $this->line('Add these lines to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);

        return self::SUCCESS;
    }
}
