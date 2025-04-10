# UI Utilities

The UI utilities provide helper functions for DOM manipulation and element creation.

## Functions

### applyStyles
Applies CSS classes to an element.

```typescript
function applyStyles(element: HTMLElement, styleObj: string): void
```

#### Parameters
- `element` (HTMLElement): Target element
- `styleObj` (string): CSS class names

### createElement
Creates a new DOM element with optional styling and content.

```typescript
function createElement<K extends keyof HTMLElementTagNameMap>(
  type: K,
  styleObj?: string,
  content?: El | El[],
  elProps?: Partial<HTMLElementTagNameMap[K]>,
): HTMLElementTagNameMap[K]
```

#### Parameters
- `type` (K): HTML element type
- `styleObj` (string, optional): CSS class names
- `content` (El | El[], optional): Element content
- `elProps` (Partial<HTMLElementTagNameMap[K]>, optional): Element properties

#### Returns
- `HTMLElementTagNameMap[K]`: Created element

### nlToBr
Converts newlines to HTML line breaks.

```typescript
function nlToBr(content: string): string
```

#### Parameters
- `content` (string): Text content

#### Returns
- `string`: HTML with line breaks

## Types

### El
Union type for element content.

```typescript
type El = string | HTMLElement | Element | false | undefined;
```

## Features

- Type-safe element creation
- Flexible content handling
- CSS class application
- Newline conversion
- Property assignment
- Array content support

## Usage Example

```typescript
// Create a styled div
const div = createElement('div', 'my-class', 'Hello World');

// Create element with multiple children
const container = createElement('div', 'container', [
  createElement('h1', 'title', 'Title'),
  createElement('p', 'content', 'Content')
]);

// Apply styles
applyStyles(div, 'new-class');

// Convert newlines
const text = nlToBr('Line 1\nLine 2');
// Result: 'Line 1<br>Line 2'
```

## Notes

- `createElement` supports both string and element content
- Array content is flattened and filtered
- Properties are assigned using Object.assign
- Style classes are applied using className
- Newline conversion preserves existing HTML 