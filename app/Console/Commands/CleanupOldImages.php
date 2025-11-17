<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Services\SupabaseStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup {--days=90 : Number of days to keep images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete package images older than specified days (default: 90 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up images older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");
        
        // Find packages with images older than cutoff date
        $packages = Package::whereNotNull('package_image')
            ->where('created_at', '<', $cutoffDate)
            ->get();
        
        $deletedCount = 0;
        $failedCount = 0;
        $supabase = new SupabaseStorageService();
        
        foreach ($packages as $package) {
            if (empty($package->package_image)) {
                continue;
            }
            
            try {
                // Extract path from Supabase URL
                $path = $supabase->extractPathFromUrl($package->package_image);
                
                if ($path) {
                    // Delete from Supabase
                    if ($supabase->delete($path)) {
                        // Clear image URL from database
                        $package->package_image = null;
                        $package->save();
                        $deletedCount++;
                        $this->line("Deleted: {$package->tracking_code} - {$path}");
                    } else {
                        $failedCount++;
                        $this->warn("Failed to delete: {$package->tracking_code} - {$path}");
                    }
                } else {
                    // Invalid URL format, just clear from database
                    $package->package_image = null;
                    $package->save();
                    $deletedCount++;
                    $this->line("Cleared invalid URL: {$package->tracking_code}");
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Error cleaning up image', [
                    'package_id' => $package->id,
                    'tracking_code' => $package->tracking_code,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Error processing {$package->tracking_code}: {$e->getMessage()}");
            }
        }
        
        $this->info("Cleanup completed!");
        $this->info("Deleted: {$deletedCount} images");
        if ($failedCount > 0) {
            $this->warn("Failed: {$failedCount} images");
        }
        
        Log::info('Image cleanup completed', [
            'days' => $days,
            'deleted' => $deletedCount,
            'failed' => $failedCount,
        ]);
        
        return Command::SUCCESS;
    }
}
