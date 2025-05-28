# Chat

## Additional controls

You can expose additional controls (select boxes) to the chat, both text and image mode. This allows you to present user with additional options, like selecting specific model or image quality.

### Format
To expose additional controls, update system setting `modai.chat.additional_controls` with JSON that will follow this schema:

```JSON
{
  "type": "object",
  "properties": {
    "image": {
      "description": "Additional controles for the image mode",
      "type": "array",
      "items": [
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string",
              "description": "Name of the option, will be passed to the model"
            },
            "label": {
              "type": "string",
              "description": "Display name for the user"
            },
            "icon": {
              "type": "string",
              "description": "Optional icon in a form of SVG HTML tag"
            },
            "values": {
              "type": "object",
              "description": "value: label pairs, where `value` will be passed to the model and `label` will be displayed to the user"
            }
          },
          "required": [
            "name",
            "label",
            "values"
          ]
        }
      ]
    },
    "text": {
      "description": "Additional controles for the text mode",
      "type": "array",
      "items": [
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string",
              "description": "Name of the option, will be passed to the model"
            },
            "label": {
              "type": "string",
              "description": "Display name for the user"
            },
            "icon": {
              "type": "string",
              "description": "Optional icon in a form of SVG HTML tag"
            },
            "values": {
              "type": "object",
              "description": "value: label pairs, where `value` will be passed to the model and `label` will be displayed to the user"
            }
          },
          "required": [
            "name",
            "label",
            "values"
          ]
        }
      ]
    }
  }
}
```

### Example
Here's an example that will let user in text mode to select `Defaul`, `4o mini` or `4o` model and in the image mode `Default`, `Low` or `High` quality.

```JSON
{
  "image": [
    {
      "name": "quality",
      "label": "Quality",
      "values": {
        "low": "Low",
        "high": "High"
      }
    }
  ],
  "text": [
    {
      "name": "model",
      "icon": "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-activity'><path d='M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2'></path></svg>",
      "label": "Model",
      "values": {
        "openai/gpt-4o-mini": "4o mini",
        "openai/gpt-4o": "4o"
      }
    }
  ]
}

```
