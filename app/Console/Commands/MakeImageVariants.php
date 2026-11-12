<?php

namespace App\Console\Commands;

use App\Support\ImageStore;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('fdh:image-variants')]
#[Description('Write the smaller copies of every uploaded photo that does not have them yet (safe to run again).')]
class MakeImageVariants extends Command
{
    public function handle(ImageStore $images): int
    {
        $written = $images->makeAllVariants();

        $this->info("{$written} resized copies written.");

        return self::SUCCESS;
    }
}
