# LearnHub Multi-Language Implementation Guide

## Overview
LearnHub now supports 5 languages:
-  English (en)
- French / Français (fr)
-  Arabic / العربية (ar) - with RTL support
-  Italian / Italiano (it)
-  German / Deutsch (de)

## How It Works

### Configuration Files
- **config/languages.php** - Core language configuration system
- **lang/en.php** - English translations
- **lang/fr.php** - French translations
- **lang/ar.php** - Arabic translations (RTL)
- **lang/it.php** - Italian translations
- **lang/de.php** - German translations

### Key Features

#### 1. Session-Based Language Management
The system uses PHP sessions to store the user's language preference. Default language is English.

#### 2. Translation Function
Use `__('key')` to translate any text:
```php
echo __('welcome_back'); // Outputs "Welcome Back!" in English
```

#### 3. RTL Support
Arabic language automatically applies RTL (right-to-left) text direction:
```php
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getLanguageDirection() ?>">
```

#### 4. Language Switcher
A dropdown in the header navigation allows users to switch languages. The selection persists across pages via sessions.

### Usage Examples

#### In PHP Files:
```php
<h1><?= __('hero_title') ?></h1>
<p><?= __('hero_subtitle') ?></p>
<button><?= __('get_started') ?></button>
```

#### Available Functions:
- `getCurrentLanguage()` - Returns current language code (e.g., 'en', 'fr')
- `getLanguageDirection()` - Returns 'rtl' for Arabic, 'ltr' for others
- `__('key')` - Translates a key to current language

### Switching Languages
Users can switch languages by:
1. Clicking the language dropdown in the header
2. Selecting their preferred language
3. The page will reload with all content in the selected language

### Adding New Translations

#### Step 1: Add to all language files
Edit each file in the `lang/` directory and add your key:

**lang/en.php:**
```php
'new_key' => 'English text',
```

**lang/fr.php:**
```php
'new_key' => 'Texte français',
```

**lang/ar.php:**
```php
'new_key' => 'النص العربي',
```

And so on for it.php and de.php.

#### Step 2: Use in your code
```php
echo __('new_key');
```

### Translation Keys Reference

#### Navigation
- home, contact, about, login, register, get_started, logout
- dashboard, profile, teacher_dashboard, my_learning

#### Hero Section
- hero_title, hero_subtitle, create_course, my_courses

#### Stats
- enrolled_students, courses_created, instructors

#### Features
- features_title, assignments, quizzes, gradebook
- course_materials, student_management, analytics
- (Each with corresponding _desc keys)

#### Forms
- email_address, password, confirm_password, full_name
- remember_me, forgot_password, sign_in, sign_up
- i_want_to, learn, teach

#### Contact & About
- contact_us, email, phone, address, name, subject, message
- about_us, our_mission, our_vision, why_choose
- ready_to_start, get_started_today

#### Footer
- quick_links, connect, all_rights_reserved, simple_lms

### RTL Styling

Arabic language applies these CSS rules automatically:
```css
body[dir="rtl"] {
    text-align: right;
}
body[dir="rtl"] .navbar-nav {
    margin-left: 0 !important;
    margin-right: auto !important;
}
body[dir="rtl"] .dropdown-menu-end {
    right: auto !important;
    left: 0 !important;
}
```

### Files Updated with Translations

✅ **config/config.php** - Includes language system
✅ **includes/header.php** - Language switcher, RTL support, translated navigation
✅ **includes/footer.php** - Translated footer links
✅ **index.php** - Fully translated landing page
✅ **auth/login.php** - Translated login form
✅ **auth/register.php** - Translated registration form
✅ **contact.php** - Translated contact page
✅ **about.php** - Translated about page

### Testing

To test the multi-language system:

1. **Open the website** at `http://localhost/learnhub`
2. **Click the language dropdown** (flag + language name) in the top navigation
3. **Select a language** (English, Français, العربية, Italiano, or Deutsch)
4. **Verify the translation** by navigating through pages:
   - Home page
   - Login page
   - Register page
   - Contact page
   - About page
5. **Test RTL** by selecting Arabic (العربية) - text should align right

### Browser Session Management

The language preference is stored in `$_SESSION['language']` and persists until:
- User manually changes language
- Browser session ends
- Session is cleared

### Performance Notes

- Translations are loaded once per page load
- No database queries required for translations
- Minimal performance impact (~80 translation keys per language)
- Cached in session for optimal speed

### Future Enhancements

Possible improvements:
- Add more languages
- Store language preference in database for logged-in users
- Implement language detection based on browser settings
- Add date/time localization
- Currency formatting per locale
- Pluralization support

## Support

For questions or issues with the multi-language system, check:
1. Session is started in config.php
2. Translation files exist in lang/ directory
3. Keys exist in all language files
4. Using `__('key')` function correctly

---

**Note**: All 5 languages are fully implemented with complete translations for the entire application interface.
