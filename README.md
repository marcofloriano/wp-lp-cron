# WP LP Cron

**WordPress plugin to display customizable countdown timers on pages and WooCommerce products**

**WP LP Cron** is a very simple plugin that allows you to display countdown timers (days, hours, minutes, and seconds) on selected WordPress pages and WooCommerce products. Ideal for time-limited promotions, launches, events, and deadlines.

## Features

- Display a countdown timer on:
  - Selected WordPress pages
  - WooCommerce product pages
- Set end **date and time** via admin interface
- Add a **title** and **description** to the banner
- Timer **automatically disappears** when it ends
- **Enable/disable automatic banner injection** on pages (avoids duplication when using shortcode)
- Display the timer manually anywhere using the `[wp_lp_cron]` **shortcode**
- Full **styling customization** for shortcode elements:
  - Font **color**
  - Font **size**
  - **Inline or block layout** for:
    - Title
    - Description
    - Timer
- Choose between multiple **visual templates**:
  - ✅ **Default**
  - 💡 **Neon Digital**
  - 🟥 **Promotional Box** (eCommerce style)
- Shortcode is also restricted to selected pages/products (like the banner)
- JavaScript countdown with live updates and "Expired" fallback
- Automatically removes expired timers from all pages

## Installation

1. Upload the `wp-lp-cron` folder to the `/wp-content/plugins/` directory, or install via the WordPress plugin dashboard.
2. Activate the plugin from the **Plugins** menu.
3. Go to the **WP LP Cron** settings page to configure the countdown timer.

## How to Use

### Automatic Banner Display

1. In the admin dashboard, navigate to **WP LP Cron**.
2. Select the pages and/or WooCommerce products where the timer should appear.
3. Set the **end date/time**, **title**, and **description**.
4. Choose a **visual style** (e.g., Neon, Promo Box)
5. Enable or disable **automatic banner injection**.
6. Save changes.

> When enabled, the banner appears at the top of selected pages and disappears after expiration.

### Manual Display with Shortcode

Use the shortcode below to manually add the timer anywhere (post, page, widget, template):

```php
[wp_lp_cron]
