# Prompt Library

Through system setting `modai.chat.prompt_library` you can define a set of prompts for both `text` and `image` chats, that the user will be able to quickly use.

## Example

```JSON
{
  "text": [
    {
      "name": "MODX",
      "children": [
        {
          "name": "Blog",
          "children": [
            {
              "name": "Step 1",
              "value": "Say hi"
            },
            {
              "name": "Step 2",
              "value": "Say hello"
            }
          ]
        },
        {
          "name": "Dev",
          "children": [
            {
              "name": "Step 1",
              "value": "Say I'm dev"
            },
            {
              "name": "Step 2",
              "value": "Say yo!"
            }
          ]
        }
      ]
    }
  ],
  "image": [
    {
      "name": "Action Figure",
      "children": [
        {
          "name": "Blog",
          "value": "Create an image of an action figure like box, where the figure will be a MODX developer. Additional items in the box will be macbook, iphone and a book."
        },
    }
  ]
}

```
