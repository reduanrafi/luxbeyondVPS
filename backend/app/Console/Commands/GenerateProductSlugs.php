<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateProductSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing products that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::whereNull('slug')->orWhere('slug', '')->get();
        
        if ($products->isEmpty()) {
            $this->info('All products already have slugs.');
            return 0;
        }

        $this->info("Generating slugs for {$products->count()} products...");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $product->slug = Product::generateUniqueSlug($product->name, $product->id);
            $product->saveQuietly(); // Use saveQuietly to avoid triggering events
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Slugs generated successfully!');

        return 0;
    }
}

