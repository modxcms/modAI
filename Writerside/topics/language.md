# Language Module

The language module provides internationalization support for the application, allowing for translation of text strings and messages.

## Lng Type

Defines the translation function signature:

```typescript
type Lng = (key: string, params?: Record<string, string>) => string;
```

### Parameters
- `key` (string): Translation key
- `params` (Record<string, string>, optional): Parameters to interpolate into the translation

### Returns
- `string`: Translated text

## Usage

The language module uses a translation function provided in the global configuration. If no translation function is provided, it falls back to a default implementation.

### Example

```typescript
// Basic translation
const greeting = lng('greeting.hello');

// Translation with parameters
const welcome = lng('greeting.welcome', { name: 'User' });
```

## Configuration

The translation function is configured through the global state:

```typescript
globalState.config.translateFn = (key, params) => {
  // Custom translation logic
  return translatedText;
};
```

## Features

- Supports parameter interpolation in translations
- Fallback mechanism for missing translations
- Centralized translation management
- Type-safe translation keys and parameters 