<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama produk')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                FileUpload::make('image')
                    ->label('Gambar produk')
                    ->image()
                    ->disk('public')
                    ->directory('products'),
                Toggle::make('active')
                    ->label('Aktif dijual')
                    ->default(true),
            ]);
    }
}
