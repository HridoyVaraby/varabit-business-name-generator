# Project Brief: Varabit Business Name Generator

## Overview
The Varabit Business Name Generator is a WordPress plugin that helps users generate creative and relevant business name ideas based on keywords, tone, and industry preferences. The plugin leverages Google Gemini 2.0 Flash API to generate business name suggestions.

## Core Requirements

### Frontend
- Form with inputs for:
  - Keywords (text input)
  - Tone selection (dropdown - Fun, Professional, Trendy, Luxurious)
  - Industry/niche selection (dropdown - Tech, Fashion, Health, Food, etc.)
  - "Generate Names" button
- AJAX-based submission to avoid page reload
- Display 10-20 name suggestions in a clean list/grid format
- Each name includes a "Copy" button
- Optional domain availability check

### Backend
- Admin Settings Page with:
  - Secure OpenAI API key storage
  - GPT model selection (gpt-3.5-turbo, gpt-4)
  - Toggle for domain check feature
  - Optional style customization settings
- Shortcode implementation: `[varabit_name_generator]`

## Technical Requirements
- WordPress best practices (enqueue scripts/styles, nonce for AJAX)
- AJAX communication via admin-ajax.php
- Mobile-responsive design
- Separate CSS file for styling
- Standard plugin folder structure: `/varabit-business-name-generator/`
- Function naming convention: prefix with `varabit_`
- Security measures:
  - No API key exposure in frontend
  - Nonce verification in AJAX
  - Input sanitization

## Bonus Features
- "Generate More" button to re-trigger with same input
- "Copy All" button

## Deliverables
- Complete, functional plugin in a ZIP-ready folder
- Shortcode implementation
- Clean, commented code
- README with installation and usage instructions
- Admin settings page
- Frontend business name generator UI