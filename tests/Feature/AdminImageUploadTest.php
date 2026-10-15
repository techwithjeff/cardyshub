<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Images uploaded through the admin have to land somewhere a browser can reach.
 *
 * They did not. Every FileUpload omitted ->disk('public'), so files went to the
 * default disk — `local`, i.e. storage/app/private — while every display
 * component and the storefront read from the `public` disk. The upload appeared
 * to work because the thumbnail you see before saving is Livewire's temporary
 * file, so the failure only showed up later as a broken image on the shop.
 */
class AdminImageUploadTest extends TestCase
{
    use RefreshDatabase;

    // fake()->create() with an explicit mime rather than fake()->image(): the
    // latter needs the GD extension to generate a real image, which not every
    // machine running these tests has.
    protected function setUp(): void
    {
        parent::setUp();

        \Filament\Facades\Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_a_product_image_lands_on_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $category = Category::create(['name' => 'Serums', 'slug' => 'serums']);
        $file = UploadedFile::fake()->create('serum.jpg', 40, 'image/jpeg');

        Livewire::test(\App\Filament\Admin\Resources\Products\Pages\CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name' => 'Upload Test Serum',
                'slug' => 'upload-test-serum',
                'price_pence' => 1999,
                'stock_quantity' => 5,
                'status' => 'published',
                'images' => [['path' => [$file], 'alt' => 'A serum']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'upload-test-serum')->firstOrFail();
        $image = $product->images()->firstOrFail();

        // The stored path must resolve on the disk the storefront serves from.
        Storage::disk('public')->assertExists($image->path);

        // And must NOT be sitting on the private disk, unreachable.
        Storage::disk('local')->assertMissing($image->path);
    }

    public function test_the_stored_path_matches_the_public_url_the_shop_requests(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Serums', 'slug' => 'serums']);

        Livewire::test(\App\Filament\Admin\Resources\Products\Pages\CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name' => 'Url Test Serum',
                'slug' => 'url-test-serum',
                'price_pence' => 1999,
                'stock_quantity' => 5,
                'status' => 'published',
                'images' => [['path' => [UploadedFile::fake()->create('x.jpg', 40, 'image/jpeg')], 'alt' => null]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $image = Product::where('slug', 'url-test-serum')->firstOrFail()->images()->firstOrFail();

        // The storefront renders asset('storage/'.$path); the file must be under
        // the products directory on the public disk for that to resolve.
        $this->assertStringStartsWith('products/', $image->path);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_a_brand_logo_lands_on_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        Livewire::test(\App\Filament\Admin\Resources\Brands\Pages\CreateBrand::class)
            ->fillForm([
                'name' => 'Upload Test Brand',
                'slug' => 'upload-test-brand',
                'logo' => [UploadedFile::fake()->create('logo.png', 40, 'image/png')],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $brand = Brand::where('slug', 'upload-test-brand')->firstOrFail();

        $this->assertNotNull($brand->logo);
        Storage::disk('public')->assertExists($brand->logo);
        Storage::disk('local')->assertMissing($brand->logo);
    }

    public function test_every_admin_upload_field_names_the_public_disk(): void
    {
        // A guard rather than a behaviour test: a new FileUpload added without
        // ->disk('public') reintroduces exactly this bug, and it is invisible
        // until an image 404s in production.
        $files = [
            app_path('Filament/Admin/Resources/Products/Schemas/ProductForm.php'),
            app_path('Filament/Admin/Resources/Brands/Schemas/BrandForm.php'),
            app_path('Filament/Admin/Resources/Categories/Schemas/CategoryForm.php'),
            app_path('Filament/Admin/Pages/ManageSettings.php'),
        ];

        foreach ($files as $file) {
            $source = file_get_contents($file);
            $uploads = substr_count($source, 'FileUpload::make');
            $disks = substr_count($source, "->disk('public')");

            $this->assertSame(
                $uploads,
                $disks,
                basename($file).' has '.$uploads.' upload field(s) but '.$disks." ->disk('public') call(s)."
            );
        }
    }
}
