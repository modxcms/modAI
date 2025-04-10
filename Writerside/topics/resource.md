# Resource Module

The resource module provides functionality for managing and enhancing resource fields in the application, particularly focusing on image handling and content management.

## Features

### Image Plus Integration
- Enhanced image field handling
- AI-powered image alt text generation
- Image source management
- Custom image actions

### Content Management
- Resource content field integration
- Template variable (TV) support
- Custom field handling

## Configuration

The module accepts a configuration object with the following structure:

```typescript
type Config = {
  tvs: [number, string][];        // Template variables
  resourceFields: string[];       // Resource field names
};
```

## Functions

### attachImagePlus
Enhances an image field with AI capabilities.

#### Parameters
- `imgPlusPanel` (Element): The image plus panel element
- `fieldName` (string): Name of the field

#### Features
- AI-powered image selection
- Alt text generation
- Image source management
- Custom image actions

### attachContent
Attaches AI capabilities to the resource content field.

### attachTVs
Manages template variables with AI integration.

### attachResourceFields
Handles custom resource fields with AI capabilities.

## Usage Example

```typescript
// Initialize resource module
initOnResource({
  tvs: [
    [1, 'image_tv'],
    [2, 'description_tv']
  ],
  resourceFields: ['content', 'introtext']
});

// The module will automatically enhance the specified fields with AI capabilities
```

## Integration Points

- Image fields
- Content editor
- Template variables
- Custom resource fields

## AI Features

- Image alt text generation
- Content suggestions
- Field value optimization
- Context-aware assistance 