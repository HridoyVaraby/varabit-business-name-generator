# System Patterns: Varabit Business Name Generator

## System Architecture
The Varabit Business Name Generator follows a standard WordPress plugin architecture with clear separation between frontend and backend components:

```
Varabit Business Name Generator
├── Frontend Layer
│   ├── Form Interface (HTML/CSS)
│   ├── JavaScript Handlers (AJAX, UI interactions)
│   └── Results Display
│       └── Domain Availability Information
├── Backend Layer
│   ├── Shortcode Processing
│   ├── AJAX Request Handling
│   ├── API Integration
│   │   ├── Google Gemini API (Name Generation)
│   │   └── Domain Availability API
│   └── Admin Settings Management
└── Data Layer
    └── WordPress Options API (settings storage)
```

## Key Technical Decisions
- **WordPress Integration**: Using standard WordPress hooks and APIs for seamless integration
- **AJAX Implementation**: Asynchronous requests to prevent page reloads and provide better UX
- **API Abstraction**: Encapsulating API calls in dedicated functions for maintainability
- **Responsive Design**: Mobile-first approach to ensure usability across devices
- **Security Focus**: Implementing WordPress security best practices throughout

## Design Patterns in Use
- **Singleton Pattern**: For main plugin class to prevent multiple instantiations
- **Factory Pattern**: For generating different types of API requests
- **Observer Pattern**: Using WordPress action/filter hooks system
- **MVC-like Structure**:
  - Model: API interaction and data processing
  - View: Frontend display and shortcode output
  - Controller: AJAX handlers and business logic

## Component Relationships
- **Form → AJAX Handler**: User inputs are collected and sent via AJAX
- **AJAX Handler → API Service**: Processes requests and calls the API service
- **API Service → Google Gemini API**: Communicates with external API for name generation
- **API Service → Results Formatter**: Processes API response into usable format
- **Results Formatter → Domain API**: Checks domain availability for generated names (if enabled)
- **Domain API → Results Formatter**: Adds domain availability information to results
- **Results Formatter → Frontend Display**: Sends formatted results back to frontend
- **Admin Settings → API Service**: Configures API behavior based on settings
- **Admin Settings → Domain API**: Controls whether domain checking is enabled

## Critical Implementation Paths
1. **Plugin Initialization**:
   - Register scripts, styles, shortcodes
   - Set up admin menu and settings

2. **Shortcode Rendering**:
   - Process shortcode attributes
   - Output HTML form
   - Enqueue necessary scripts/styles

3. **AJAX Processing**:
   - Validate and sanitize inputs
   - Prepare API request
   - Process API response
   - Check domain availability (if enabled)
   - Return formatted results

4. **Domain Availability Checking**:
   - Convert business names to domain-friendly format
   - Perform DNS lookup to check availability
   - Add availability information to results
   - Generate registration links for available domains

5. **Settings Management**:
   - Register settings
   - Create settings page
   - Handle settings validation and storage