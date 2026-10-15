<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                            ->columnSpan(2),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Select::make('brand_id')
                            ->label('Brand')
                            ->options(fn () => Brand::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('sku')
                            ->label('SKU'),
                        TextInput::make('size')
                            ->placeholder('e.g. 30ml'),
                        TextInput::make('concern')
                            ->placeholder('e.g. Dark spots'),
                        TextInput::make('skin_type')
                            ->placeholder('e.g. All skin types'),
                        TextInput::make('key_active')
                            ->label('Key active')
                            ->placeholder('e.g. Niacinamide 10%'),
                        TextInput::make('short_description')
                            ->columnSpan(2),
                        RichEditor::make('description')
                            ->columnSpan(2),
                        Textarea::make('how_to_use')
                            ->label('How to use')
                            ->rows(3)
                            ->columnSpan(2),
                        Textarea::make('ingredients_inci')
                            ->label('Full ingredients (INCI)')
                            ->helperText('Placeholder only — replace with the manufacturer\'s real list before launch.')
                            ->rows(3)
                            ->columnSpan(2),
                    ]),

                Section::make('Pricing & stock')
                    ->columns(2)
                    ->components([
                        TextInput::make('price_pence')
                            ->label('Price (£)')
                            ->numeric()
                            ->required()
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('compare_at_price_pence')
                            ->label('Compare-at price (£)')
                            ->numeric()
                            ->prefix('£')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                        TextInput::make('stock_quantity')
                            ->label('Stock quantity')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),
                        Toggle::make('is_featured')
                            ->label('Featured on homepage'),
                    ]),

                Section::make('Images')
                    ->components([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                // disk('public') is load-bearing. Without it the
                                // upload goes to the DEFAULT disk, which is
                                // `local` — storage/app/private — where no
                                // browser can reach it. The thumbnail still
                                // appears (that is Livewire's temporary file),
                                // so the save looks like it worked right up
                                // until the image 404s on the shop.
                                // visibility('public') is about file ACLs and
                                // does not choose the disk.
                                FileUpload::make('path')
                                    ->image()
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required(),
                                TextInput::make('alt')
                                    ->label('Alt text'),
                            ])
                            ->orderColumn('sort_order')
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['alt'] ?? 'Image')
                            ->addActionLabel('Add image'),
                    ]),

                Section::make('Variants')
                    ->description('Optional — e.g. different sizes or scents. Leave empty for a single-variant product.')
                    ->components([
                        Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. 50ml'),
                                TextInput::make('price_pence')
                                    ->label('Price override (£)')
                                    ->numeric()
                                    ->prefix('£')
                                    ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                                    ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                                TextInput::make('stock_quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                TextInput::make('sku')
                                    ->label('SKU'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Variant')
                            ->addActionLabel('Add variant'),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->components([
                        TextInput::make('meta_title'),
                        TextInput::make('meta_description'),
                    ]),
            ]);
    }
}
