# 🗓️ Calendar Timer - WordPress Plugin

> **Countdown to the nearest event in Hurry Timer style with multiple countdowns support and automatic year change.**

[![WordPress](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.5-orange.svg)](https://github.com/konradxmalinowski/calendar-wp-extension)

## ✨ Features

- 🎯 **Smart Countdown System** - Automatically shows the next upcoming event
- 🔄 **Multiple Countdowns** - Support for multiple shortcodes on the same page
- 📅 **Automatic Year Update** - Events automatically roll over to the next year
- 🎨 **Beautiful UI** - Modern, responsive design with smooth animations
- 🚀 **AJAX Powered** - Real-time updates without page refresh
- 🔒 **Security First** - Built with WordPress security best practices
- 📱 **Mobile Friendly** - Responsive design for all devices

## 🚀 Quick Start

### 1. Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/calendar-timer/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Create your first event using the new "Wydarzenia" post type

### 2. Basic Usage

Simply add the shortcode anywhere on your page:

```php
[calendar_countdown]
```

This will display a beautiful countdown to your next upcoming event.

## 📖 Usage Examples

### Basic Countdown

```php
[calendar_countdown]
```

Shows the first upcoming event.

### Multiple Countdowns

```php
[calendar_countdown]
[calendar_countdown]
[calendar_countdown]
```

All shortcodes without offset show the same event.

### Specific Event Position

```php
[calendar_countdown offset="0"]  <!-- First event -->
[calendar_countdown offset="1"]  <!-- Second event -->
[calendar_countdown offset="2"]  <!-- Third event -->
```

## 🛠️ How It Works

### Event Management

1. **Create Events**: Use the custom post type "Wydarzenia" (Events)
2. **Set Date & Time**: Use the datetime picker in the sidebar
3. **Automatic Updates**: Events automatically update to the next year

### Countdown Logic

- Events are sorted by date/time
- Countdown automatically switches to the next event when current ends
- Rate limiting prevents abuse (max 10 requests per minute per IP)
- Nonce verification ensures security

## 🔧 Technical Details

### Requirements

- WordPress 5.0+
- PHP 7.4+
- Modern web browser with JavaScript enabled

### Security Features

- ✅ Nonce verification for all forms
- ✅ User capability checks
- ✅ Input sanitization and validation
- ✅ Rate limiting for AJAX requests
- ✅ SQL injection protection via WordPress Query API
- ✅ XSS protection with proper escaping

### File Structure

```
calendar-timer/
├── Calendar.php          # Main plugin file
├── README.md            # This file
└── .gitignore          # Git ignore file
```

## 🎨 Customization

### Styling

The plugin uses inline styles for easy customization. You can override them with CSS:

```css
.calendar-countdown {
  /* Your custom styles */
}

.calendar-countdown h3 {
  /* Custom title styles */
}

.countdown-timer {
  /* Custom timer styles */
}
```

### JavaScript Events

The plugin fires custom events you can listen to:

```javascript
document.addEventListener('calendar_event_loaded', function (e) {
  console.log('New event loaded:', e.detail);
});
```

## 🐛 Troubleshooting

### Common Issues

**Q: Countdown shows "Brak nadchodzących wydarzeń"**
A: Make sure you have created events with future dates in the "Wydarzenia" post type.

**Q: Countdown doesn't update automatically**
A: Check if JavaScript is enabled and there are no console errors.

**Q: Events don't show up**
A: Verify the event date is set correctly in the datetime picker.

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. 🍴 Fork the repository
2. 🔧 Create a feature branch (`git checkout -b feature/amazing-feature`)
3. 💾 Commit your changes (`git commit -m 'Add amazing feature'`)
4. 📤 Push to the branch (`git push origin feature/amazing-feature`)
5. 🔄 Open a Pull Request

### Development Setup

1. Clone the repository
2. Set up a local WordPress development environment
3. Activate the plugin
4. Make your changes
5. Test thoroughly

## 📝 Changelog

### Version 1.5

- ✨ Added multiple countdown support
- 🔒 Enhanced security with nonce verification and rate limiting
- 🎨 Improved UI with better countdown display
- 🚀 Added automatic event switching
- 📱 Improved mobile responsiveness

### Version 1.0

- 🎯 Initial release
- 📅 Basic countdown functionality
- 🎨 Simple UI design

## 📄 License

This project is licensed under the **GPL v2** License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Konrad Malinowski**

- 🌐 Website: [https://github.com/konradxmalinowski](https://github.com/konradxmalinowski)
- 📧 Contact: [konradxmalinowski](https://github.com/konradxmalinowski)

## 🙏 Acknowledgments

- WordPress community for the amazing platform
- Contributors and testers
- Open source community for inspiration

## ⭐ Support

If you find this plugin helpful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📢 Sharing with others

---

**Made with ❤️ for the WordPress community**
