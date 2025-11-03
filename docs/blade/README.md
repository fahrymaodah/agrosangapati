# Blade Templates Documentation

**Last Updated**: November 3, 2025

> Best practices, fixes, and patterns untuk Laravel Blade templates dalam AgroSangapati project.

---

## 📚 Documentation Files

### 1. [Template Patterns](./templates.md) 🎨
**Contains**:
- Fixed template structures
- Proper Filament wrapper usage
- Icon sizing fixes
- Responsive layouts
- Under development pages

### 2. [Components](./components.md) 🧩
**Contains**:
- Custom Blade components
- Component composition
- Slot usage patterns
- Props & attributes

---

## 🔧 Common Fixes Applied

### ✅ Icon Sizing Fixed
**Before** (Wrong):
```blade
<div class="w-24 h-24">
    <svg class="w-full h-full">...</svg> <!-- 96px x 96px - TOO BIG -->
</div>
```

**After** (Correct):
```blade
<div class="p-3 bg-purple-100 rounded-lg inline-block">
    <svg class="w-8 h-8 text-purple-600">...</svg> <!-- 32px x 32px - Perfect -->
</div>
```

### ✅ Filament Wrapper Usage
**Before** (Wrong):
```blade
<div class="p-6">
    <!-- Direct content without wrapper -->
</div>
```

**After** (Correct):
```blade
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Content with proper spacing -->
    </div>
</x-filament-panels::page>
```

### ✅ Responsive Grid Layout
```blade
<!-- ✅ Proper responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Grid items -->
</div>
```

---

## 📋 Template Categories

### 1. Dashboard Pages
- Financial Dashboard
- Production Dashboard  
- Marketing Dashboard
- Consolidated Dashboard

### 2. Report Pages
- Financial Reports (4 tabs)
- Production Reports
- Sales Reports

### 3. Management Pages
- User Management
- Activity Log
- System Settings

### 4. Under Development Pages
- Placeholder templates
- Coming soon notices
- Feature previews

---

## 🎨 Design System

### Color Palette
```blade
<!-- Status Colors -->
<div class="bg-green-100 text-green-800">Success</div>
<div class="bg-yellow-100 text-yellow-800">Warning</div>
<div class="bg-red-100 text-red-800">Error</div>
<div class="bg-blue-100 text-blue-800">Info</div>
<div class="bg-purple-100 text-purple-800">Feature</div>
```

### Spacing Scale
```blade
<!-- Consistent spacing -->
<div class="space-y-6">        <!-- Between major sections -->
<div class="space-y-4">        <!-- Between cards -->
<div class="space-y-2">        <!-- Between small elements -->
<div class="space-x-3">        <!-- Horizontal spacing -->
```

### Border Radius
```blade
<div class="rounded-xl">       <!-- Large cards -->
<div class="rounded-lg">       <!-- Medium elements -->
<div class="rounded-full">     <!-- Badges, icons -->
```

---

## 🔗 Related Documentation

- [Filament Guide](../filament/) - Filament v4 documentation
- [Tailwind Config](../tailwind/) - Tailwind customization
- [Frontend Implementation](../../FRONTEND_IMPLEMENTATION.md) - Current status

---

## 📞 Resources

- **Laravel Blade**: https://laravel.com/docs/blade
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Filament Components**: https://filamentphp.com/docs/components

---

**Project**: AgroSangapati  
**Framework**: Laravel 11.x + Blade + Tailwind CSS 3.x
