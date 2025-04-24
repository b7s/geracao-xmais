<?php

namespace App\Filament\Associado\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ProfilePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static string $view = 'filament.associado.pages.profile';
    
    protected static ?string $navigationLabel = 'Meu Perfil';
    
    protected static ?int $navigationSort = 1;
    
    public function getUser(): User
    {
        return Auth::user();
    }
} 