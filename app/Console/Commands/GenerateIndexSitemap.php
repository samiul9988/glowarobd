<?php
namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Page;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\BlogCategory;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapIndex;
use Illuminate\Support\Facades\Artisan;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;

class GenerateIndexSitemap extends Command
{
    protected $signature = 'sitemap:index';
    protected $description = 'Generate the index sitemap';

    public function handle()
    {
        // Artisan::call('sitemap:static');
        // $this->info('Static sitemap generated successfully.');
        Artisan::call('sitemap:products');
        $this->info('Product sitemaps generated successfully.');
        Artisan::call('sitemap:categories');
        $this->info('Category sitemaps generated successfully.');
        Artisan::call('sitemap:brands');
        $this->info('Brand sitemaps generated successfully.');
        Artisan::call('sitemap:blogs');
        $this->info('Blog sitemaps generated successfully.');
        // Artisan::call('sitemap:blogs-categories');
        // $this->info('Blog categories sitemaps generated successfully.');
        Artisan::call('sitemap:pages');
        $this->info('Page sitemaps generated successfully.');

        $sitemapIndex = SitemapIndex::create();
        // For Static Pages
        // if(file_exists(public_path('sitemaps/sitemap-static.xml'))) {
        //     $sitemapIndex->add(SitemapTag::create('sitemaps/sitemap-static.xml')->setLastModificationDate(now()));
        // }

        // Add product sitemaps
        $productCount = Product::published()->count();
        $chunkSize = 1000;
        $sitemapCount = ceil($productCount / $chunkSize);

        for ($i = 0; $i < $sitemapCount; $i++) {
            $fileName = "sitemaps/sitemap-products-".($i+1).".xml";
            if(file_exists(public_path($fileName))) {
                $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
                ->setLastModificationDate(now()));
            }
        }

        // Add category sitemaps
        $categoryCount = Category::active()->count();
        $chunkSize = 1000;
        $sitemapCount = ceil($categoryCount / $chunkSize);

        for ($i = 0; $i < $sitemapCount; $i++) {
            $fileName = "sitemaps/sitemap-categories-".($i+1).".xml";
            if(file_exists(public_path($fileName))) {
                $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
                ->setLastModificationDate(now()));
            }
        }

        // Add brand sitemaps
        $brandCount = Brand::query()->count();
        $chunkSize = 1000;
        $sitemapCount = ceil($brandCount / $chunkSize);

        for ($i = 0; $i < $sitemapCount; $i++) {
            $fileName = "sitemaps/sitemap-brands-".($i+1).".xml";
            if(file_exists(public_path($fileName))) {
                $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
                ->setLastModificationDate(now()));
            }
        }

        // Add blog sitemaps
        $blogCount = Blog::active()->count();
        $chunkSize = 1000;
        $sitemapCount = ceil($blogCount / $chunkSize);

        for ($i = 0; $i < $sitemapCount; $i++) {
            $fileName = "sitemaps/sitemap-blogs-".($i+1).".xml";
            if(file_exists(public_path($fileName))) {
                $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
                ->setLastModificationDate(now()));
            }
        }

        // Add blog category sitemaps
        // $blogCategoryCount = BlogCategory::count();
        // $chunkSize = 1000;
        // $sitemapCount = ceil($blogCategoryCount / $chunkSize);

        // for ($i = 0; $i < $sitemapCount; $i++) {
        //     $fileName = "sitemaps/sitemap-blogs-categories-".($i+1).".xml";
        //     if(file_exists(public_path($fileName))) {
        //         $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
        //         ->setLastModificationDate(now()));
        //     }
        // }

        // Add pages sitemaps
        $pageCount = Page::count();
        $chunkSize = 1000;
        $sitemapCount = ceil($pageCount / $chunkSize);

        for ($i = 0; $i < $sitemapCount; $i++) {
            $fileName = "sitemaps/sitemap-pages-".($i+1).".xml";
            if(file_exists(public_path($fileName))) {
                $sitemapIndex->add(SitemapTag::create(to_frontend($fileName, 'sitemaps'))
                ->setLastModificationDate(now()));
            }
        }

        // Save the index sitemap
        $sitemapIndex->writeToFile(public_path('sitemaps/sitemap.xml'));

        $this->info('Index sitemap generated successfully.');
    }
}
