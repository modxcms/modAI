# Loading Overlay

The loading overlay component provides a visual indicator for loading states, with customizable appearance and behavior.

## Options

### TextareaOverlayOptions
Configuration options for the overlay.

```typescript
type TextareaOverlayOptions = {
  indicatorType?: 'spinner' | 'dots';  // Type of loading indicator
  overlayColor?: string;               // Overlay background color
  indicatorColor?: string;             // Indicator color
};
```

## Functions

### createLoadingOverlay
Creates a loading overlay on a specified element.

```typescript
function createLoadingOverlay(
  element: HTMLElement,
  options: TextareaOverlayOptions = {}
): {
  remove: () => void;
  show: () => void;
  hide: () => void;
}
```

#### Parameters
- `element` (HTMLElement): Target element for the overlay
- `options` (TextareaOverlayOptions): Configuration options

#### Returns
Object with overlay control methods:
- `remove`: Removes the overlay
- `show`: Shows the overlay
- `hide`: Hides the overlay

## Features

- Automatic indicator type selection based on element size
- Customizable colors
- Smooth animations
- Responsive design
- Easy integration
- Multiple indicator types

## Usage Example

```typescript
// Create overlay with default options
const overlay = createLoadingOverlay(myElement);

// Show the overlay
overlay.show();

// Hide the overlay
overlay.hide();

// Remove the overlay
overlay.remove();

// Create with custom options
const customOverlay = createLoadingOverlay(myElement, {
  indicatorType: 'dots',
  overlayColor: 'rgba(0, 0, 0, 0.5)',
  indicatorColor: '#ffffff'
});
```

## Styling

The overlay uses CSS animations for smooth transitions:
- Spinner animation for larger elements
- Dots animation for smaller elements
- Customizable colors through options
- Responsive positioning
- Inherits border radius from target element 