<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ReleaseExpiredBookingHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:release-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melepaskan slot booking pending yang masa hold pembayarannya sudah melewati batas waktu 15 menit';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa slot booking yang kedaluwarsa...');

        $releasedCount = Booking::releaseExpiredHolds();

        if ($releasedCount > 0) {
            $this->info("Berhasil melepaskan {$releasedCount} slot booking yang kedaluwarsa.");
        } else {
            $this->comment('Tidak ada slot booking yang kedaluwarsa saat ini.');
        }

        return Command::SUCCESS;
    }
}
