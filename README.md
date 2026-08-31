# Simple CRM

Custom WordPress CRM plugin built from scratch.

## Features

- Custom post types:
  - Contacts
  - Companies
  - Deals
  - Activities
- Elementor Pro integration:
  - Two different forms with different fields
  - Automatic creation of contacts, companies, and deals
- Kanban pipeline:
  - Stages: Lead, Qualified, Proposal, Won, Lost
  - Drag-and-drop deal cards
  - Values shown in euros
- Export:
  - CSV export for contacts, companies, deals, activities
- Email:
  - Send emails to contacts directly from the CRM admin
  - Emails logged as activities
- Lark Base sync:
  - Push contacts and deals to a Lark Base called "CRM"
  - Configurable via WordPress options

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Elementor Pro (for form integration)

## Installation

1. Clone or download this repository.
2. Upload the `simple-crm` folder to `/wp-content/plugins/`.
3. Activate the plugin from the WordPress admin.
4. Configure Lark Base (optional):
   - Set `simple_crm_lark_app_id`
   - Set `simple_crm_lark_app_secret`
   - Set `simple_crm_lark_base_token`
   - Set `simple_crm_lark_contacts_table_id`
   - Set `simple_crm_lark_deals_table_id`

## Usage

- CRM menu in admin:
  - Contacts
  - Companies
  - Deals
  - Activities
  - Deals Kanban
  - Export

- Use shortcode `[crm_contact_form]` for a basic contact form.
- For Elementor:
  - Create forms named:
    - `Contact Form`
    - `Deal Request Form`
  - Map field IDs as used in `Class_CRM_Elementor`.

## License

MIT
