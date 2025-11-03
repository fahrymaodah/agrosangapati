# Filament 4.x Guidelines

## Namespace Structure

### Forms (Create/Edit Pages)
```php
// Form inputs
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;

// Form helpers
use Filament\Forms\Get; // for closures

// Layout components
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;

// Base schema
use Filament\Schemas\Schema;
```

### Infolists (View Pages)
```php
// Display entries
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;

// Layout components
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

// Base schema
use Filament\Schemas\Schema;
```

### Tables (List Pages)
```php
// Columns
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;

// Layout (Tables have their own Split)
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
```

## Important Notes

### ❌ Don't Use These (Don't Exist)
```php
// These don't exist in Filament 4.x:
use Filament\Forms\Components\Section; // ❌ Section is in Schemas
use Filament\Infolists\Components\Section; // ❌ Section is in Schemas
use Filament\Infolists\Components\Split; // ❌ Split only exists in Tables
use Filament\Infolists\Components\Grid; // ❌ Grid is in Schemas
```

### ✅ Correct Methods
```php
// TextEntry methods
TextEntry::make('field')
    ->label('Label')
    ->helperText('Helper text') // ✅ Not ->description()
    ->icon('heroicon-o-icon')
    ->badge()
    ->color('success');

// Layout
Section::make('Title') // ✅ From Filament\Schemas\Components
    ->schema([...]);

Grid::make(2) // ✅ From Filament\Schemas\Components
    ->schema([...]);
```

## Package Architecture
- **filament/schemas** - Base UI components (Section, Grid, etc.)
- **filament/forms** - Extends schemas with form inputs
- **filament/infolists** - Extends schemas with display entries  
- **filament/tables** - Extends schemas with table components

## Common Fixes
1. Move Section/Grid imports to `Filament\Schemas\Components`
2. Replace Split with Grid in infolists
3. Use `helperText()` instead of `description()` for TextEntry
4. Remove non-existent relationships from eager loading