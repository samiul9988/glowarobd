<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class GenerateFacebookFeed extends Command
{
    protected $signature = 'feed:facebook';
    protected $description = 'Generate Facebook Marketing Product Feed XML';

    public function handle()
    {
        $filePath = 'facebook-feed.xml'; // public/facebook-feed.xml

        $this->info('Generating Facebook product feed at ' . now()->format('d-m-Y h:i:s') . '...');

        // Start XML (use put for first write to avoid blank line)
        Storage::disk('public')->put($filePath,
            '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL .
            '<channel>' . PHP_EOL .
            '<title>' . config('app.name') . '</title>' . PHP_EOL .
            '<link>' . config('app.frontend') . '</link>' . PHP_EOL .
            '<description>Facebook Product Catalog Feed</description>'
        );

        \App\Models\Product::published()
            ->with('stocks', 'category')
            ->chunkById(1000, function ($products) use ($filePath) {
                foreach ($products as $product) {
                    $item = [];
                    $item[] = '<item>';
                    $item[] = '<g:id>' . $product->id . '</g:id>';
                    $item[] = '<g:title><![CDATA[' . $product->name . ']]></g:title>';
                    $item[] = '<g:description><![CDATA[' . strip_tags($product->description) . ']]></g:description>';
                    $item[] = '<g:link>' . to_frontend(route('product', $product->slug)) . '</g:link>';
                    $item[] = '<g:image_link>' . uploaded_asset($product->thumbnail_img) . '</g:image_link>';
                    $item[] = '<g:brand>Glowaro</g:brand>';
                    $item[] = '<g:condition>new</g:condition>';
                    $item[] = '<g:availability>' . (check_in_stock($product) ? 'in stock' : 'out of stock') . '</g:availability>';
                    $item[] = '<g:price>' . str_replace(',', '', number_format(getMinimumPriceByVariant($product, $product->stocks->first()), 2)) . ' BDT</g:price>';
                    $item[] = '<g:product_type><![CDATA[' . ($product->category->name ?? 'Uncategorized') . ']]></g:product_type>';
                    $item[] = '</item>';

                    Storage::disk('public')->append($filePath, implode(PHP_EOL, $item));
                }
            });

        // Close XML
        Storage::disk('public')->append($filePath,
            '</channel>' . PHP_EOL .
            '</rss>'
        );

        $this->info('Feed successfully generated at ' . now()->format('d-m-Y h:i:s'));
    }

}
