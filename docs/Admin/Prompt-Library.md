---
sidebar_position: 4
---

# Prompt Library

## Purpose
The **Prompt Library** is a centralized place to store and manage reusable prompts and prompt snippets, such as:
- Brand voice and tone guidelines
- SEO optimization templates
- QA or review checklists
- Reusable task patterns (e.g., “Summarize this page for our CEO”, “Rewrite this in plain, non-technical language a 12 year old can understand”)

## Prompt Types and Visibility
In the library you can typically designate prompts as:
- **Public** – Available to all users who can access modAI.
- **Private** – Only visible to the user who created them.

This is useful for:
- Team‑wide standard prompts (public).
- Personal workflows.

Categories can also be marked public or private, governed by their own permissions (`modai_admin_prompt_library_prompt_save_public`, `modai_admin_prompt_library_category_save_public`). See [Permissions](../Configuration/Permissions.md).

## Categories

Prompts are organized into categories, which can be nested. modAI seeds two top-level categories on install:

- **Text** – for text/textarea prompts.
- **Image** – for image-generation prompts.

Add your own categories and subcategories beneath these to keep reusable prompts organized by team, channel, or task.

## Managing the Library

The Prompt Library has its own Manager component (tab) where you can create, edit, categorize, and delete prompts and categories. Access is controlled by the `modai_admin_prompt_library*` permissions — for example, `modai_admin_prompt_library` to open the tab, `modai_admin_prompt_library_prompt_save` to create or update prompts, and `modai_admin_prompt_library_prompt_delete` to remove them.
