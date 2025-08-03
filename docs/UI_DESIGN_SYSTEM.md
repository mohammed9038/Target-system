# UI Design System - Target Management System

## Overview
This document outlines the unified design system implemented across the Target Management System to ensure consistency, professionalism, and excellent user experience.

## Design Principles

### 1. Consistency
- Uniform components across all pages
- Standardized spacing and typography
- Consistent color usage and iconography

### 2. Professional Appearance
- Clean, modern interface design
- Proper use of whitespace and visual hierarchy
- High-contrast, accessible color schemes

### 3. User Experience
- Intuitive navigation and layout
- Responsive design for all devices
- Clear visual feedback and interactions

## Color Palette

### Primary Colors
- **Primary**: `#2563eb` (Blue 600)
- **Primary Dark**: `#1d4ed8` (Blue 700)
- **Primary Light**: `#3b82f6` (Blue 500)
- **Primary 50**: `#eff6ff` (Very light blue)
- **Primary 100**: `#dbeafe` (Light blue)

### Secondary Colors
- **Secondary**: `#64748b` (Slate 500)
- **Secondary 50**: `#f8fafc` (Very light gray)
- **Secondary 100**: `#f1f5f9` (Light gray)
- **Secondary 700**: `#334155` (Dark gray)
- **Secondary 900**: `#0f172a` (Very dark gray)

### Status Colors
- **Success**: `#10b981` (Emerald 500)
- **Warning**: `#f59e0b` (Amber 500)
- **Danger**: `#ef4444` (Red 500)
- **Info**: `#06b6d4` (Cyan 500)

## Typography

### Font Family
- **Primary**: Inter (Google Fonts)
- **Fallback**: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif

### Font Sizes
- **XS**: 0.75rem (12px)
- **SM**: 0.875rem (14px)
- **Base**: 1rem (16px)
- **LG**: 1.125rem (18px)
- **XL**: 1.25rem (20px)
- **2XL**: 1.5rem (24px)
- **3XL**: 1.875rem (30px)
- **4XL**: 2.25rem (36px)

## Layout Structure

### Application Layout
```
┌─────────────────────────────────────────────┐
│                App Topbar                   │
├─────────────┬───────────────────────────────┤
│             │                               │
│   Sidebar   │         Main Content         │
│             │                               │
│             │                               │
└─────────────┴───────────────────────────────┘
```

### Sidebar
- **Width**: 280px
- **Background**: Linear gradient from Primary 800 to Primary 900
- **Color**: White text with transparency variations
- **Navigation**: Grouped sections with icons

### Main Content
- **Top Bar**: 80px height with user profile and breadcrumbs
- **Content Area**: Flexible with proper padding and spacing

## Components

### 1. Page Header Component
**Usage**: `<x-page_header title="..." description="..." icon="...">`

**Features**:
- Consistent title typography (3XL, bold)
- Icon with background circle
- Optional description text
- Actions slot for buttons

### 2. Card Component
**Usage**: `<x-card title="..." icon="...">`

**Features**:
- Clean white background with subtle shadow
- Optional header with title and actions
- Proper padding and spacing
- Rounded corners (12px)

### 3. Button Component
**Usage**: `<x-button variant="primary" icon="bi-plus">`

**Variants**:
- `primary`: Blue background
- `outline-primary`: Blue border, transparent background
- `success`: Green background
- `outline-success`: Green border
- `danger`: Red background

**Sizes**:
- `sm`: Small (32px height)
- `md`: Medium (40px height) - default
- `lg`: Large (48px height)

### 4. Alert Component
**Usage**: `<x-alert type="success">`

**Types**:
- `success`: Green with check icon
- `danger`: Red with warning icon
- `warning`: Amber with warning icon
- `info`: Cyan with info icon

**Features**:
- Auto-dismissible option
- Icon integration
- Proper contrast ratios

## Spacing System

### Standard Spacing Scale
- **XS**: 0.25rem (4px)
- **SM**: 0.5rem (8px)
- **MD**: 1rem (16px)
- **LG**: 1.5rem (24px)
- **XL**: 2rem (32px)
- **2XL**: 3rem (48px)

### Layout Spacing
- **Page padding**: 2rem (32px)
- **Component spacing**: 1.5rem (24px)
- **Element spacing**: 1rem (16px)

## Shadows

### Shadow Scale
- **XS**: Subtle element shadows
- **SM**: Card and button shadows
- **MD**: Modal and dropdown shadows
- **LG**: Sidebar and major component shadows
- **XL**: Major overlay shadows

## Icons

### Icon Library
- **Primary**: Bootstrap Icons
- **CDN**: `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css`

### Icon Usage Guidelines
- Use semantic icons that clearly represent functionality
- Maintain consistent icon sizes within components
- Prefer outline style icons for better accessibility

## Responsive Design

### Breakpoints
- **SM**: 576px and up
- **MD**: 768px and up
- **LG**: 992px and up
- **XL**: 1200px and up

### Mobile Adaptations
- Collapsible sidebar with toggle button
- Stacked page headers on small screens
- Responsive table scrolling
- Touch-friendly button sizes

## Accessibility

### Color Contrast
- All text meets WCAG 2.1 AA standards
- Minimum 4.5:1 contrast ratio for normal text
- Minimum 3:1 contrast ratio for large text

### Keyboard Navigation
- Full keyboard accessibility
- Focus indicators on all interactive elements
- Logical tab order

### Screen Reader Support
- Semantic HTML structure
- ARIA labels where appropriate
- Alternative text for icons

## Implementation Guidelines

### File Structure
```
resources/views/
├── layouts/
│   └── app.blade.php (Main layout)
├── components/
│   ├── page_header.blade.php
│   ├── card.blade.php
│   ├── button.blade.php
│   └── alert.blade.php
└── [pages]/
    └── *.blade.php (Individual pages)
```

### Component Usage
1. Always use the standardized components
2. Follow the established patterns for new pages
3. Maintain consistent spacing and typography
4. Use the defined color palette only

### CSS Organization
- Global styles defined in main layout
- Component-specific styles in component files
- Utility classes for common modifications
- CSS custom properties for theme values

## Quality Checklist

Before deploying any UI changes, ensure:

- [ ] Consistent component usage across pages
- [ ] Proper color contrast ratios
- [ ] Responsive design tested on multiple devices
- [ ] Keyboard navigation works properly
- [ ] Loading states are implemented
- [ ] Error states are handled gracefully
- [ ] Icons are semantic and consistent
- [ ] Typography scale is followed
- [ ] Spacing system is used correctly
- [ ] All interactive elements have hover states

## Browser Support

### Minimum Requirements
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Progressive Enhancement
- Core functionality works in all supported browsers
- Enhanced features degrade gracefully
- CSS Grid and Flexbox for layout
- CSS Custom Properties for theming

---

*This design system ensures a consistent, professional, and user-friendly interface across the entire Target Management System.*
