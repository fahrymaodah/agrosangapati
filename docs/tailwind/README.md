# Tailwind CSS Documentation

**Last Updated**: November 3, 2025

> Tailwind CSS configuration and customization untuk AgroSangapati project.

---

## 📚 Documentation Files

### 1. [Customization Guide](./customization.md) 🎨
**Contains**:
- Theme configuration
- Color palette customization
- Typography settings
- Spacing & sizing
- Custom plugins

### 2. [Utility Reference](./utilities.md) 📖
**Contains**:
- Common utility classes
- Responsive modifiers
- State variants
- Custom utilities

---

## 🎨 Project Theme

### Color Palette
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          500: '#22c55e',  // Main green
          600: '#16a34a',
          900: '#14532d',
        },
        secondary: {
          500: '#a16207',  // Brown/earth tone
        },
        accent: {
          500: '#f97316',  // Orange/harvest
        }
      }
    }
  }
}
```

### Typography
```javascript
fontFamily: {
  sans: ['Inter', 'system-ui', 'sans-serif'],
  mono: ['Fira Code', 'monospace'],
}
```

### Spacing
```javascript
spacing: {
  '18': '4.5rem',
  '88': '22rem',
}
```

---

## 🚀 Common Patterns

### Card Layout
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <!-- Card content -->
</div>
```

### Grid System
```html
<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <!-- Grid items -->
</div>
```

### Flex Utilities
```html
<!-- Center content -->
<div class="flex items-center justify-center">
  <!-- Centered content -->
</div>

<!-- Space between -->
<div class="flex items-center justify-between">
  <!-- Content -->
</div>
```

### Text Utilities
```html
<!-- Heading -->
<h2 class="text-2xl font-bold text-gray-900">Title</h2>

<!-- Body text -->
<p class="text-gray-600 leading-relaxed">Description text</p>

<!-- Small text -->
<span class="text-sm text-gray-500">Helper text</span>
```

---

## 🎯 Best Practices

### 1. Use Semantic Class Names
```html
<!-- ✅ Good -->
<div class="card">
<div class="btn-primary">

<!-- ❌ Avoid -->
<div class="bg-white rounded-lg p-4 shadow">
```

### 2. Responsive Design
```html
<!-- ✅ Mobile-first approach -->
<div class="text-base md:text-lg lg:text-xl">
  Responsive text
</div>
```

### 3. Consistent Spacing
```html
<!-- ✅ Use spacing scale consistently -->
<div class="space-y-4">  <!-- 1rem -->
<div class="space-y-6">  <!-- 1.5rem -->
<div class="space-y-8">  <!-- 2rem -->
```

### 4. Color Consistency
```html
<!-- ✅ Use theme colors -->
<div class="bg-primary-500 text-white">
<div class="bg-secondary-100 text-secondary-800">

<!-- ❌ Avoid arbitrary colors -->
<div class="bg-[#22c55e]">
```

---

## 🔧 Build Configuration

### Development
```bash
npm run dev
```

### Production Build
```bash
npm run build
```

### Watch Mode
```bash
npm run watch
```

---

## 📦 Plugins Used

- **@tailwindcss/forms** - Better form styles
- **@tailwindcss/typography** - Prose classes
- **@tailwindcss/aspect-ratio** - Aspect ratio utilities

---

## 🔗 Related Documentation

- [Filament Guide](../filament/) - Filament styling
- [Blade Templates](../blade/) - Template patterns
- [Frontend Implementation](../../FRONTEND_IMPLEMENTATION.md) - Current status

---

## 📞 Resources

- **Tailwind Docs**: https://tailwindcss.com/docs
- **Tailwind Play**: https://play.tailwindcss.com
- **Tailwind UI**: https://tailwindui.com

---

**Project**: AgroSangapati  
**Framework**: Tailwind CSS 3.x + Laravel 11.x
