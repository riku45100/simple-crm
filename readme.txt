=== Simple CRM ===
Contributors: Richard McGrath/FourZero.work
Tags: crm, contacts, deals, pipeline, elementor, lark, kanban, leads, sales
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom WordPress CRM with Elementor integration, Lark Base sync, Kanban pipeline, exports, and email.

== Description ==

Simple CRM is a lightweight, self-hosted CRM built as a WordPress plugin. Manage contacts, companies, deals, and activities directly in your WordPress dashboard.

= Features =

- **Contacts, Companies, Deals, Activities** – Custom post types for your CRM data.
- **Elementor Pro integration** – Automatically create contacts and deals from Elementor forms.
- **Kanban pipeline** – Visual deal board with stages: Lead, Qualified, Proposal, Won, Lost.
- **Export to CSV** – Export contacts, companies, deals, and activities.
- **Email from CRM** – Send emails to contacts and log them as activities.
- **Lark Base sync** – Push contacts and deals to a Lark Base “CRM” table.

Use it as your main CRM or as a front-end for Lark, Airtable, or other tools.

= Who is this for? =

- Small businesses and freelancers who want a self-hosted CRM.
- Agencies managing leads and deals inside WordPress.
- Teams already using Lark Base and wanting WordPress as a front-end.

== Installation ==

1. Upload the `simple-crm` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **CRM** in the admin menu to start adding contacts and deals.
4. (Optional) Configure Lark Base under **CRM → Lark Settings**.
5. (Optional) Connect your Elementor forms named “Contact Form” and “Deal Request Form”.

== Frequently Asked Questions ==

= Does this work without Elementor Pro? =

Yes. The core CRM, Kanban, exports, and email features work without Elementor. Elementor Pro is only needed for automatic form-to-CRM integration.

= Can I use this with Lark Base only? =

You can use Simple CRM as a lightweight front-end and sync everything to Lark Base. All data is stored in WordPress first, then pushed to Lark.

= Is my data exported easily? =

Yes. Go to **CRM → Export** to download CSV files for contacts, companies, deals, and activities.

= Can I customize the pipeline stages? =

Currently the stages are hard-coded: Lead, Qualified, Proposal, Won, Lost. You can change them in the code via `simple_crm_get_deal_stages()`.

== Screenshots ==

1. Deals Kanban board with drag-and-drop stages.
2. Contact list with email, phone, and company columns.
3. Lark Settings page for Base integration.
4. Export page to download CSV files.

== Changelog ==

= 0.1.0 =
* Initial release.
* Contacts, Companies, Deals, Activities CPTs.
* Elementor Pro form integration.
* Kanban deal pipeline (Lead, Qualified, Proposal, Won, Lost).
* CSV export for all entities.
* Send email to contacts from admin.
* Lark Base sync for contacts and deals.
