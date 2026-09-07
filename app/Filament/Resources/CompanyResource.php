<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'SaaS Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Syarikat'),
                Forms\Components\TextInput::make('ssm')
                    ->maxLength(255)
                    ->label('No. SSM'),
                Forms\Components\TextInput::make('cidb')
                    ->maxLength(255)
                    ->label('No. CIDB'),
                Forms\Components\TextInput::make('tokens_left')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Baki Token Laporan'),
                Forms\Components\Select::make('package_id')
                    ->relationship('package', 'name')
                    ->nullable()
                    ->label('Pakej Langganan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nama Syarikat'),
                Tables\Columns\TextColumn::make('ssm')->searchable()->label('SSM'),
                Tables\Columns\TextColumn::make('package.name')->badge()->color('success')->label('Pakej Aktif'),
                Tables\Columns\TextColumn::make('tokens_left')->sortable()->label('Baki Token'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Tarikh Daftar'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/index'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/edit/{record}'),
        ];
    }
}