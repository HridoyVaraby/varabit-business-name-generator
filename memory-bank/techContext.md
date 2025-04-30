# Tech Context: Varabit Business Name Generator

## Technologies Used

### Core Technologies
- **PHP**: Primary backend language for WordPress plugin development
- **JavaScript**: Frontend interactivity and AJAX communication
- **HTML/CSS**: Frontend structure and styling
- **WordPress Plugin API**: Integration with WordPress core
- **Google Gemini API**: AI service for generating business name suggestions
- **DNS Lookup**: For domain availability checking

### Libraries & Frameworks
- **jQuery**: Simplifies DOM manipulation and AJAX requests (included with WordPress)
- **WordPress REST API**: For structured data exchange
- **WordPress Options API**: For storing plugin settings

## Development Setup
- **Local WordPress Environment**: Standard WordPress installation
- **Plugin Directory Structure**:
  ```
  varabit-business-name-generator/
  ├── assets/
  │   ├── css/
  │   │   └── varabit-name-generator.css
  │   └── js/
  │       └── varabit-name-generator.js
  ├── includes/
  │   ├── class-varabit-name-generator.php
  │   ├── class-varabit-name-generator-admin.php
  │   ├── api/
  │   │   ├── class-varabit-name-generator-api.php
  │   │   └── class-varabit-name-generator-domain-api.php
  ├── templates/
  │   ├── admin-settings.php
  │   └── generator-form.php
  ├── index.php
  ├── README.md
  ├── uninstall.php
  └── varabit-business-name-generator.php
  ```

## Technical Constraints
- **WordPress Compatibility**: Must work with WordPress 5.0+
- **PHP Version**: Compatible with PHP 7.4+
- **Browser Support**: Modern browsers (Chrome, Firefox, Safari, Edge)
- **API Limitations**: Subject to Google Gemini API rate limits and token constraints
- **WordPress Plugin Guidelines**: Must adhere to WordPress.org plugin guidelines

## Dependencies
- **WordPress Core**: Requires WordPress installation
- **Google Gemini API Key**: Users must obtain their own API key
- **Internet Connection**: Required for API communication

## Tool Usage Patterns

### WordPress Hooks
- **Actions**: For plugin initialization, admin menu setup, AJAX processing
- **Filters**: For modifying output or behavior

### Data Management
- **Options API**: For storing and retrieving plugin settings
- **Transients API**: Optional caching for API responses

### Security Practices
- **Nonce Verification**: For all AJAX requests
- **Capability Checks**: For admin functionality
- **Data Sanitization**: For all user inputs
- **Data Escaping**: For all outputs

### API Communication
- **wp_remote_post()**: For making API requests to Google Gemini
- **JSON Processing**: For handling API responses
- **checkdnsrr()**: PHP function for DNS-based domain availability checking

### Frontend Integration
- **wp_enqueue_script/style()**: For proper asset loading
- **add_shortcode()**: For embedding functionality in posts/pages
- **wp_localize_script()**: For passing data to JavaScript