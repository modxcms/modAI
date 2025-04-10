# History Module

The history module provides a state management system for tracking and navigating through historical values, with support for UI synchronization and context preservation.

## Types

### Cache
Represents a cache of historical values for a specific key.

```typescript
type Cache<C = unknown> = {
  visible: number;           // Current visible index
  values: string[];         // Array of historical values
  context: C;              // Associated context data
  syncUI?: (data: DataOutput<C>, noStore?: boolean) => void;  // UI synchronization callback
};
```

### DataOutput
Represents the output data structure for history operations.

```typescript
type DataOutput<C = unknown> = {
  value: string;           // Current value
  nextStatus: boolean;     // Whether next operation is available
  prevStatus: boolean;     // Whether previous operation is available
  current: number;         // Current position (1-based)
  total: number;           // Total number of values
  context: C;              // Associated context data
};
```

## API

### init
Initializes a new history cache for a specific key.

#### Parameters
- `key` (string): Unique identifier for the history
- `syncUI` (function, optional): UI synchronization callback
- `initValue` (string, optional): Initial value
- `context` (C, optional): Initial context data

### insert
Inserts a new value into the history.

#### Parameters
- `key` (string): History key
- `value` (string): Value to insert
- `noStore` (boolean, optional): Whether to skip storing the value

### next
Moves to the next value in the history.

#### Parameters
- `key` (string): History key

### prev
Moves to the previous value in the history.

#### Parameters
- `key` (string): History key

## Usage Example

```typescript
// Initialize history
history.init('myField', (data) => {
  // Update UI with new value
  field.value = data.value;
});

// Insert new value
history.insert('myField', 'New value');

// Navigate history
const nextValue = history.next('myField');
const prevValue = history.prev('myField');
```

## Features

- Value history tracking
- Navigation (next/previous)
- UI synchronization
- Context preservation
- Optional value storage
- Type-safe context data 