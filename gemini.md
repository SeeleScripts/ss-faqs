# Gemini Project Information

## Project Overview

- **Name:** Sakura Store FAQs (ss-faqs)
- **Purpose:** Provide a custom FAQ system for the Sakura Store WordPress site with Secure Custom Fields (SCF) integration, optional WooCommerce linking, and full WPGraphQL support.
- **Repository:** https://github.com/SeeleScripts/SakuraStore (plugin lives under `wp-content/plugins/ss-faqs`)

## Core Dependencies

- **Secure Custom Fields (SCF)** – fork of ACF, required for custom fields (likes counter, product relationship). The plugin checks for the presence of `class_exists('ACF')` or the `acf_add_local_field_group` function.
- **WooCommerce** – optional; when active, a `Producto` taxonomy term and a product relationship field are added.
- **WPGraphQL** – required for exposing the FAQ post type, taxonomy, likes field, and mutation to increment likes.

## File Structure

```
ss-faqs/
├── ss-faqs.php                # Main plugin bootstrap (singleton, dependency checks)
├── README.md                  # Installation, usage, and GraphQL examples
├── includes/
│   ├── class-ss-faqs-post-type.php      # Registers `ss-faqs` CPT
│   ├── class-ss-faqs-taxonomies.php     # Registers `faq-type` taxonomy & default terms
│   ├── class-ss-faqs-fields.php         # Registers SCF field groups (likes, product link)
│   ├── class-ss-faqs-woocommerce.php    # WooCommerce detection & product term handling
│   └── class-ss-faqs-graphql.php        # WPGraphQL type registration & mutation
└── assets/ (optional for future UI)
```

## Key Features

- **Custom Post Type** `ss-faqs` – title = question, content = answer.
- **Taxonomy** `faq-type` with default terms: General, Usuario, Envios y Entregas, Devoluciones y Cambios.
- **Likes Counter** – numeric SCF field, default 0, exposed via GraphQL.
- **Product Relationship** – SCF post‑object field linking to a WooCommerce product (only when WooCommerce is active).
- **GraphQL** – queries for all FAQs, FAQs filtered by product, and a mutation `incrementFaqLikes`.

## Future Maintenance Notes

- **Adding New Fields:** Extend `class-ss-faqs-fields.php` and register additional SCF field groups inside the `acf/init` hook.
- **Additional Taxonomies:** Create new taxonomy classes similar to `class-ss-faqs-taxonomies.php` and register them in the main plugin init.
- **GraphQL Extensions:** To expose new fields or mutations, update `class-ss-faqs-graphql.php` and ensure they are added to the appropriate GraphQL type.
- **Compatibility Checks:** When updating WordPress core or dependencies, verify that the SCF API (`acf_add_local_field_group`) remains unchanged.
- **Testing:** Use WPGraphQL IDE or a GraphQL client to run the example queries in the README after any code changes.

## License

GPLv2 or later – see the `LICENSE` file in the repository.
